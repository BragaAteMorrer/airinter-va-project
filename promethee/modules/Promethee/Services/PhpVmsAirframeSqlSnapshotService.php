<?php

namespace Modules\Promethee\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class PhpVmsAirframeSqlSnapshotService
{
    public const DEFAULT_RELATIVE_PATH = 'promethee/airframes/phpvms-airframes-snapshot.sql';

    /**
     * Export the legacy phpVMS fleet source plus the Promethee Airframes rows
     * materialised from it. The generated SQL is intended to be re-runnable on
     * the same schema: every INSERT uses ON DUPLICATE KEY UPDATE.
     */
    public function export(?string $absolutePath = null): array
    {
        $source = DB::connection('prometheus');
        $target = DB::connection();

        foreach (['subfleets', 'fares', 'subfleet_fare', 'aircraft'] as $table) {
            if (!$source->getSchemaBuilder()->hasTable($table)) {
                throw new RuntimeException('Table Prometheus requise absente : '.$source->getTablePrefix().$table);
            }
        }

        foreach ([
            'promethee_aircraft_type_profiles',
            'promethee_aircraft_historical_variants',
            'promethee_aircraft_configuration_assignments',
        ] as $table) {
            if (!Schema::hasTable($table)) {
                throw new RuntimeException('Table Prométhée requise absente : '.$target->getTablePrefix().$table);
            }
        }

        $subfleetIds = $source->table('subfleets')->orderBy('id')->pluck('id')->all();
        $fareIds = $subfleetIds === []
            ? []
            : $source->table('subfleet_fare')
                ->whereIn('subfleet_id', $subfleetIds)
                ->orderBy('fare_id')
                ->pluck('fare_id')
                ->unique()
                ->values()
                ->all();

        $sourcePrefix = $source->getTablePrefix();
        $targetPrefix = $target->getTablePrefix();

        $tables = [
            $sourcePrefix.'subfleets' => $source->table('subfleets')->orderBy('id')->get(),
            $sourcePrefix.'fares' => $fareIds === []
                ? collect()
                : $source->table('fares')->whereIn('id', $fareIds)->orderBy('id')->get(),
            $sourcePrefix.'subfleet_fare' => $subfleetIds === []
                ? collect()
                : $source->table('subfleet_fare')
                    ->whereIn('subfleet_id', $subfleetIds)
                    ->orderBy('subfleet_id')
                    ->orderBy('fare_id')
                    ->get(),
            $sourcePrefix.'aircraft' => $subfleetIds === []
                ? collect()
                : $source->table('aircraft')
                    ->whereIn('subfleet_id', $subfleetIds)
                    ->orderBy('id')
                    ->get(),
            $targetPrefix.'promethee_aircraft_type_profiles' => $this->airframeRows('promethee_aircraft_type_profiles', 'data'),
            $targetPrefix.'promethee_aircraft_historical_variants' => $this->airframeRows('promethee_aircraft_historical_variants', 'data'),
            $targetPrefix.'promethee_aircraft_configuration_assignments' => $this->airframeRows('promethee_aircraft_configuration_assignments', 'overrides'),
        ];

        $absolutePath ??= storage_path('app/'.self::DEFAULT_RELATIVE_PATH);
        $directory = dirname($absolutePath);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Impossible de créer le dossier du snapshot SQL : '.$directory);
        }

        $generatedAt = now()->toIso8601String();
        $sql = [
            '-- Air Inter VA · phpVMS → Promethee Airframes snapshot',
            '-- Generated at '.$generatedAt,
            '-- Source legacy: Prometheus database connection (phpvms7_* in production)',
            '-- Target: current Promethee Airframes rows when already materialised',
            '-- Re-runnable: INSERT ... ON DUPLICATE KEY UPDATE',
            '',
            'SET NAMES utf8mb4;',
            'SET FOREIGN_KEY_CHECKS=0;',
            '',
        ];

        $counts = [];
        foreach ($tables as $table => $rows) {
            $counts[$table] = $rows->count();
            $sql[] = '-- '.$table.' · '.$rows->count().' row(s)';

            foreach ($rows as $row) {
                $sql[] = $this->upsert($table, (array) $row);
            }

            $sql[] = '';
        }

        $sql[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $sql[] = '';

        file_put_contents($absolutePath, implode("\n", $sql));

        return [
            'path' => $absolutePath,
            'generated_at' => $generatedAt,
            'counts' => $counts,
            'bytes' => filesize($absolutePath) ?: 0,
        ];
    }

    private function airframeRows(string $table, string $jsonColumn)
    {
        return DB::table($table)
            ->where(function ($query) use ($jsonColumn) {
                $query->where('source', PhpVmsAirframeImportService::SOURCE)
                    ->orWhere('notes', 'like', '%'.PhpVmsAirframeImportService::NOTE_MARKER.'%')
                    ->orWhere($jsonColumn, 'like', '%"legacy_phpvms"%');
            })
            ->orderBy('id')
            ->get();
    }

    private function upsert(string $table, array $row): string
    {
        if ($row === []) {
            throw new RuntimeException('Impossible de sérialiser une ligne vide pour '.$table);
        }

        $columns = array_keys($row);
        $quotedColumns = array_map(fn (string $column) => $this->identifier($column), $columns);
        $values = array_map(fn ($value) => $this->literal($value), array_values($row));

        $primary = in_array('id', $columns, true) ? 'id' : $columns[0];
        $updates = [];
        foreach ($columns as $column) {
            if ($column === $primary) {
                continue;
            }
            $identifier = $this->identifier($column);
            $updates[] = $identifier.'=VALUES('.$identifier.')';
        }

        if ($updates === []) {
            $identifier = $this->identifier($primary);
            $updates[] = $identifier.'=VALUES('.$identifier.')';
        }

        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s;',
            $this->identifier($table),
            implode(', ', $quotedColumns),
            implode(', ', $values),
            implode(', ', $updates)
        );
    }

    private function identifier(string $value): string
    {
        return '`'.str_replace('`', '``', $value).'`';
    }

    private function literal(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return "'".str_replace(
            ["\\", "'", "\0", "\n", "\r", "\x1a"],
            ["\\\\", "\\'", "\\0", "\\n", "\\r", "\\Z"],
            (string) $value
        )."'";
    }
}
