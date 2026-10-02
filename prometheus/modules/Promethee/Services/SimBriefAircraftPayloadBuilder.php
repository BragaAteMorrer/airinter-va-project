<?php

namespace Modules\Promethee\Services;

class SimBriefAircraftPayloadBuilder
{
    private const KG_TO_LB = 2.2046226218;

    public function build(array $profile): array
    {
        $type = strtoupper(trim((string) ($profile['type'] ?? '')));
        abort_if($type === '', 422, 'Le profil SimBrief résolu ne contient aucun type de calcul.');

        $parameters = ['type' => $type];
        $acdata = [];

        if (($profile['strategy'] ?? null) === 'proxy') {
            $config = is_array($profile['config'] ?? null) ? $profile['config'] : [];
            $actualIcao = strtoupper(trim((string) ($profile['actual_icao'] ?? '')));
            $actualName = trim((string) ($profile['actual_name'] ?? ''));

            abort_if($actualIcao === '' || $actualName === '', 422,
                'Le profil proxy sb-airframe est incomplet : ICAO réel et nom appareil sont obligatoires.');

            $acdata['icao'] = $actualIcao;
            $acdata['name'] = $actualName;

            $direct = [
                'engines', 'cat', 'equip', 'transponder', 'pbn', 'extrarmk',
                'maxpax', 'hexcode', 'per', 'paxwgt', 'bagwgt', 'ceiling',
                'cruiseoffset',
            ];
            foreach ($direct as $key) {
                $value = $config[$key] ?? null;
                if ($value !== null && $value !== '') {
                    $acdata[$key] = is_string($value) ? trim($value) : $value;
                }
            }

            $group = array_filter([
                'cat' => $acdata['cat'] ?? null,
                'equip' => $acdata['equip'] ?? null,
                'transponder' => $acdata['transponder'] ?? null,
            ], fn ($value) => $value !== null && $value !== '');
            abort_if(count($group) > 0 && count($group) < 3, 422,
                'Le profil proxy '.$actualIcao.' doit définir ensemble cat, equip et transponder, ou laisser les trois champs vides.');

            $weights = is_array($config['weights_kg'] ?? null) ? $config['weights_kg'] : [];
            foreach (['oew', 'mzfw', 'mtow', 'mlw', 'maxfuel', 'maxcargo'] as $key) {
                $kg = $weights[$key] ?? null;
                if ($kg === null || $kg === '' || !is_numeric($kg)) continue;
                $acdata[$key] = round(((float) $kg * self::KG_TO_LB) / 1000, 3);
            }

            $json = json_encode(
                $acdata,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
            );
            abort_if($json === false, 500, 'Impossible d’encoder les données appareil destinées à SimBrief.');
            $parameters['acdata'] = $json;

            $performance = is_array($config['performance'] ?? null) ? $config['performance'] : [];
            foreach (['fuelfactor', 'climb', 'cruise', 'descent'] as $key) {
                $value = trim((string) ($performance[$key] ?? ''));
                if ($value !== '') $parameters[$key] = $value;
            }
        }

        return [
            'parameters' => $parameters,
            'acdata' => $acdata,
        ];
    }

    public function maxPassengers(array $profile): ?int
    {
        $value = $profile['config']['maxpax'] ?? null;
        if ($value === null || $value === '' || !is_numeric($value)) return null;

        return max(0, (int) $value);
    }
}
