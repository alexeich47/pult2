<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('permission:cache-reset');

        foreach (['super-admin', 'admin', 'operator', 'viewer'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
    }
}
