<?php

namespace App\Policies;

use App\Models\RndProject;
use App\Models\User;

class RndProjectPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operator', 'viewer']);
    }

    public function view(User $user, RndProject $rndProject): bool
    {
        return $user->hasAnyRole(['admin', 'operator', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function update(User $user, RndProject $rndProject): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function delete(User $user, RndProject $rndProject): bool
    {
        return $user->hasRole('admin');
    }
}
