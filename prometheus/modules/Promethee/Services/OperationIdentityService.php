<?php

namespace Modules\Promethee\Services;

use App\Models\Bid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OperationIdentityService
{
    public function ensure(Bid $bid): object
    {
        $link = DB::table('promethee_operation_links')->where('bid_id', (string) $bid->id)->first();

        if (!$link) {
            $now = now();
            DB::table('promethee_operation_links')->insert([
                'operation_id' => (string) Str::uuid(),
                'bid_id' => (string) $bid->id,
                'user_id' => (int) $bid->user_id,
                'flight_id' => (string) $bid->flight_id,
                'aircraft_id' => $bid->aircraft_id,
                'status' => 'reserved',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $link = DB::table('promethee_operation_links')->where('bid_id', (string) $bid->id)->first();
        } elseif ((string) ($link->aircraft_id ?? '') !== (string) ($bid->aircraft_id ?? '')) {
            DB::table('promethee_operation_links')->where('id', $link->id)->update([
                'aircraft_id' => $bid->aircraft_id,
                'updated_at' => now(),
            ]);
            $link = DB::table('promethee_operation_links')->where('id', $link->id)->first();
        }

        return $link;
    }

    public function resolveBid(string $reference, int $userId): ?Bid
    {
        $bid = Bid::query()->where('user_id', $userId)->find($reference);
        if ($bid) {
            return $bid;
        }

        $link = DB::table('promethee_operation_links')
            ->where('operation_id', $reference)
            ->where('user_id', $userId)
            ->first();

        return $link ? Bid::query()->where('user_id', $userId)->find($link->bid_id) : null;
    }

    public function attachSimBrief(Bid $bid, ?string $simbriefId): void
    {
        $link = $this->ensure($bid);
        DB::table('promethee_operation_links')->where('id', $link->id)->update([
            'simbrief_id' => $simbriefId,
            'updated_at' => now(),
        ]);
    }

    public function attachPirep(Bid $bid, string $pirepId): void
    {
        $link = $this->ensure($bid);
        DB::table('promethee_operation_links')->where('id', $link->id)->update([
            'pirep_id' => $pirepId,
            'status' => 'prefiled',
            'updated_at' => now(),
        ]);
    }

    public function forget(Bid $bid): void
    {
        DB::table('promethee_operation_links')->where('bid_id', (string) $bid->id)->delete();
    }
}
