<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
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

    public function view(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole(['admin', 'operator', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasRole('admin');
    }
}
