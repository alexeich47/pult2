<?php

namespace App\Policies;

use App\Models\Instruction;
use App\Models\User;

class InstructionPolicy
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

    public function view(User $user, Instruction $instruction): bool
    {
        return $user->hasAnyRole(['admin', 'operator', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function update(User $user, Instruction $instruction): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function delete(User $user, Instruction $instruction): bool
    {
        return $user->hasRole('admin');
    }
}
