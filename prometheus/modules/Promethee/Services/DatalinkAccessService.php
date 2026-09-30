<?php

namespace Modules\Promethee\Services;

use App\Models\Rank;
use App\Models\User;

class DatalinkAccessService
{
    public function canOperate(?User $user): bool
    {
        if (!$user) return false;

        if ($user->hasRole('admin')
            || (method_exists($user, 'isAbleTo') && $user->isAbleTo('admin-access'))) {
            return true;
        }

        $user->loadMissing('rank');
        if (!$user->rank) return false;

        $captainHours = Rank::query()
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%captain%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%commandant%']);
            })
            ->min('hours');

        // If the installation has no Captain/Commandant rank configured,
        // fail closed instead of accidentally granting dispatch privileges.
        if ($captainHours === null) return false;

        return (int) $user->rank->hours >= (int) $captainHours;
    }
}
