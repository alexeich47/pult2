<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UnitSeeder::class,
            EmployeeSeeder::class,
            IdeaSeeder::class,
            RiskEntrySeeder::class,
            ServiceSeeder::class,
            InstructionSeeder::class,
            OkrEntrySeeder::class,
            MeetingSeeder::class,
            RndProjectSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@pult.local',
            'password' => bcrypt('password'),
        ]);

        $admin->assignRole('super-admin');
    }
}
