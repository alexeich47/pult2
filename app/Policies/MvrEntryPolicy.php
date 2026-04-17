<?php

namespace App\Policies;

use App\Models\MvrEntry;
use App\Models\User;

class MvrEntryPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operator', 'viewer']);
    }

    public function view(User $user, MvrEntry $mvrEntry): bool
    {
        return $user->hasAnyRole(['admin', 'operator', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function update(User $user, MvrEntry $mvrEntry): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function delete(User $user, MvrEntry $mvrEntry): bool
    {
        return $user->hasRole('admin');
    }
}
