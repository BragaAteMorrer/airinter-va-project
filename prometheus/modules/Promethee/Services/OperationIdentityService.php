<?php

namespace Modules\Promethee\Services;

use App\Models\Bid;
use App\Models\Pirep;

/**
 * Transitional operation identity for the Prometheus -> Promethee migration.
 *
 * No schema change is required: the historic phpVMS database remains the
 * source of truth. An active operation is the existing bid, exposed through a
 * namespaced public identifier so clients no longer depend on phpVMS naming.
 */
class OperationIdentityService
{
    private const PREFIX = 'op_';

    public function id(Bid $bid): string
    {
        return self::PREFIX.(string) $bid->id;
    }

    public function resolveBid(string $reference, int $userId): ?Bid
    {
        $bidId = str_starts_with($reference, self::PREFIX)
            ? substr($reference, strlen(self::PREFIX))
            : $reference;

        if ($bidId === '') {
            return null;
        }

        return Bid::query()
            ->where('user_id', $userId)
            ->find($bidId);
    }

    public function resolvePirep(string $reference, int $userId): ?Pirep
    {
        $operationId = str_starts_with($reference, self::PREFIX) ? $reference : self::PREFIX.$reference;

        return Pirep::query()
            ->where('user_id', $userId)
            ->where('source_name', 'Hermes ACARS ['.$operationId.']')
            ->latest('created_at')
            ->first();
    }

    public function dto(Bid $bid): array
    {
        return [
            'operation_id' => $this->id($bid),
            'bid_id' => $bid->id,
            'legacy' => true,
            'persistence' => 'phpvms_bid',
        ];
    }
}
