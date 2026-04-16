<?php

namespace App\Policies;

use App\Models\OkrEntry;
use App\Models\User;

class OkrEntryPolicy
{
    /**
     * Super-admin bypass — runs before every ability check.
     */
    public function before(User $user): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operator', 'viewer']);
    }

    public function view(User $user, OkrEntry $okrEntry): bool
    {
        return $user->hasAnyRole(['admin', 'operator', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function update(User $user, OkrEntry $okrEntry): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function delete(User $user, OkrEntry $okrEntry): bool
    {
        return $user->hasRole('admin');
    }
}
