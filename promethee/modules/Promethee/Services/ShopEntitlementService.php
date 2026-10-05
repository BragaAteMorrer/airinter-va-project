<?php
namespace Modules\Promethee\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShopEntitlementService
{
    public function active(User $user, ?string $type = null): Collection
    {
        return DB::table('promethee_shop_entitlements')
            ->where('user_id', $user->id)
            ->when($type, fn($q) => $q->where('type', $type))
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get();
    }

    public function subfleetIds(User $user): array
    {
        return $this->active($user)
            ->whereIn('type', ['aircraft_type','aircraft_variant','aircraft_premium','aircraft_rental'])
            ->where('target_type', 'subfleet')
            ->pluck('target_id')->map(fn($id) => (int) $id)->unique()->values()->all();
    }

    public function hermes(User $user): array
    {
        return $this->active($user)
            ->whereIn('type', ['hermes_theme','hermes_sound_pack','hermes_efb_skin','livery'])
            ->map(fn($e) => [
                'type'=>$e->type, 'target_type'=>$e->target_type, 'target_id'=>$e->target_id,
                'expires_at'=>$e->expires_at, 'metadata'=>$e->metadata ? json_decode($e->metadata, true) : null,
            ])->values()->all();
    }

    public function owns(User $user, string $type, ?string $targetId): bool
    {
        return $this->active($user, $type)->contains(fn($e) => (string)$e->target_id === (string)$targetId);
    }
}