<?php

namespace App\Policies;

use App\Models\RiskEntry;
use App\Models\User;

class RiskEntryPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operator', 'viewer']);
    }

    public function view(User $user, RiskEntry $entry): bool
    {
        return $user->hasAnyRole(['admin', 'operator', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function update(User $user, RiskEntry $entry): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function delete(User $user, RiskEntry $entry): bool
    {
        return $user->hasRole('admin');
    }
}
