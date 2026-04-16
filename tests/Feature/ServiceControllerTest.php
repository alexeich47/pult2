<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
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

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }

    private function makeService(array $attrs = []): Service
    {
        return Service::create(array_merge([
            'name' => 'TestSvc',
            'url' => 'test.com',
            'category' => 'Инструменты',
            'unit_id' => 'holding',
            'cost' => 10.00,
            'currency' => 'USD',
            'billing' => 'monthly',
            'next_payment' => '2025-05-01',
            'status' => 'active',
        ], $attrs));
    }

    public function test_viewer_can_list_with_totals(): void
    {
        $this->makeService(['cost' => 100, 'billing' => 'monthly']); // +100/mo
        $this->makeService(['cost' => 120, 'billing' => 'yearly']);  // +10/mo
        $this->makeService(['cost' => 50, 'billing' => 'monthly', 'status' => 'inactive']); // +0
        $this->makeService(['cost' => 0, 'billing' => 'monthly', 'status' => 'trial']); // +0

        $this->actingAs($this->userWithRole('viewer'))
            ->get('/services')
            ->assertInertia(fn ($page) => $page
                ->component('Services/Index')
                ->has('services.data', 4)
                ->where('totals.total', 4)
                ->where('totals.active', 2)
                ->where('totals.subs', 4)
                ->where('totals.monthly_total', 110) // 100 + 120/12 = 110.0 → int after round()
                ->has('categories', 10)
                ->has('currencies', 4)
                ->has('billingCycles', 3)
            );
    }

    public function test_status_filter_narrows_listed_services(): void
    {
        $this->makeService(['name' => 'A', 'status' => 'active']);
        $this->makeService(['name' => 'B', 'status' => 'trial']);
        $this->makeService(['name' => 'C', 'status' => 'inactive']);

        $this->actingAs($this->userWithRole('viewer'))
            ->get('/services?filter[status]=active')
            ->assertInertia(fn ($page) => $page
                ->has('services.data', 1)
                ->where('services.data.0.name', 'A')
                // totals still reflect all services (unfiltered aggregate)
                ->where('totals.total', 3)
            );
    }

    public function test_billing_filter(): void
    {
        $this->makeService(['billing' => 'monthly']);
        $this->makeService(['billing' => 'yearly']);
        $this->makeService(['billing' => 'once']);

        $this->actingAs($this->userWithRole('viewer'))
            ->get('/services?filter[billing]=once')
            ->assertInertia(fn ($page) => $page->has('services.data', 1));
    }

    public function test_admin_can_create_with_full_validation(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post('/services', [
                'name' => 'Notion',
                'url' => 'notion.so',
                'category' => 'Инструменты',
                'unit_id' => 'holding',
                'cost' => 16.00,
                'currency' => 'USD',
                'billing' => 'monthly',
                'next_payment' => '2025-06-01',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('services', ['name' => 'Notion', 'unit_id' => 'holding']);
    }

    public function test_store_validates_enum_fields(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post('/services', [
                'name' => '',
                'category' => 'NotACat',
                'unit_id' => 'nope',
                'cost' => -5,
                'currency' => 'XYZ',
                'billing' => 'weird',
                'status' => 'ghost',
            ])
            ->assertSessionHasErrors(['name', 'category', 'unit_id', 'cost', 'currency', 'billing', 'status']);
    }

    public function test_operator_cannot_delete(): void
    {
        $svc = $this->makeService();

        $this->actingAs($this->userWithRole('operator'))
            ->delete("/services/{$svc->id}")
            ->assertForbidden();
    }

    public function test_admin_can_delete(): void
    {
        $svc = $this->makeService();

        $this->actingAs($this->userWithRole('admin'))
            ->delete("/services/{$svc->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('services', ['id' => $svc->id]);
    }

    public function test_monthly_cost_helper_zero_for_inactive(): void
    {
        $svc = $this->makeService(['cost' => 500, 'billing' => 'monthly', 'status' => 'inactive']);

        $this->assertSame(0.0, $svc->monthlyCost());
    }

    public function test_activity_log_tracks_changes(): void
    {
        $svc = $this->makeService();
        $svc->update(['cost' => 99.99]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'service',
            'subject_id' => $svc->id,
            'description' => 'service.updated',
        ]);
    }
}
