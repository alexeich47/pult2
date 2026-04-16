<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InertiaSharedPropsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RolesAndPermissionsSeeder::class,
            UnitSeeder::class,
        ]);
    }

    public function test_dashboard_exposes_sidebar_units_and_translations(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('viewer', 'web'));

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->has('units', 12) // all units including swiftpunk root
                ->where('locale', 'en')
                ->has('supportedLocales', 3)
                ->has('translations.nav.personnel')
                ->has('auth.user.roles')
                ->where('auth.user.roles.0', 'viewer')
            );
    }

    public function test_locale_switch_persists_in_session(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('viewer', 'web'));

        $this->actingAs($user)
            ->from('/dashboard')
            ->post('/locale/uk')
            ->assertRedirect('/dashboard')
            ->assertSessionHas('locale', 'uk');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertInertia(fn ($page) => $page
                ->where('locale', 'uk')
                ->where('translations.brand.sub', 'Управління холдингом')
            );
    }

    public function test_unknown_locale_is_rejected(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('viewer', 'web'));

        $this->actingAs($user)
            ->post('/locale/xx')
            ->assertNotFound();
    }
}
