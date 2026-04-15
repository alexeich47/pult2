<?php

namespace Tests\Feature;

use App\Models\RiskEntry;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RiskEntryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }

    private function makeEntry(array $attrs = []): RiskEntry
    {
        return RiskEntry::create(array_merge([
            'type' => 'risk',
            'name' => 'Sample',
            'description' => 'desc',
            'owner_name' => 'Иванов И.',
            'status' => 'open',
            'entry_date' => '2025-04-01',
        ], $attrs));
    }

    public function test_guests_are_redirected(): void
    {
        $this->get('/risks')->assertRedirect('/login');
    }

    public function test_viewer_can_list_grouped_by_type(): void
    {
        $this->makeEntry(['type' => 'risk']);
        $this->makeEntry(['type' => 'issue', 'status' => 'in_progress']);
        $this->makeEntry(['type' => 'fuckup', 'status' => 'closed']);
        $this->makeEntry(['type' => 'workaround', 'status' => 'active']);

        $this->actingAs($this->userWithRole('viewer'))
            ->get('/risks')
            ->assertInertia(fn ($page) => $page
                ->component('Risks/Index')
                ->has('entriesByType.risk', 1)
                ->has('entriesByType.issue', 1)
                ->has('entriesByType.fuckup', 1)
                ->has('entriesByType.workaround', 1)
                ->has('types', 4)
                ->has('statusesByType.risk', 4)
                ->has('statusesByType.issue', 3)
                ->has('statusesByType.fuckup', 2)
                ->has('statusesByType.workaround', 2)
            );
    }

    public function test_display_id_prefix_per_type(): void
    {
        $r1 = $this->makeEntry(['type' => 'risk']);
        $r2 = $this->makeEntry(['type' => 'risk']);
        $i1 = $this->makeEntry(['type' => 'issue']);
        $f1 = $this->makeEntry(['type' => 'fuckup']);
        $w1 = $this->makeEntry(['type' => 'workaround', 'status' => 'active']);

        $this->assertSame('R-001', $r1->fresh()->display_id);
        $this->assertSame('R-002', $r2->fresh()->display_id);
        $this->assertSame('I-001', $i1->fresh()->display_id);
        $this->assertSame('F-001', $f1->fresh()->display_id);
        $this->assertSame('W-001', $w1->fresh()->display_id);
    }

    public function test_per_type_status_validation(): void
    {
        // risk does not allow 'active'
        $this->actingAs($this->userWithRole('admin'))
            ->post('/risks', [
                'type' => 'risk',
                'name' => 'X',
                'description' => 'y',
                'owner_name' => 'Z',
                'status' => 'active',
                'entry_date' => '2025-04-01',
            ])
            ->assertSessionHasErrors('status');

        // workaround allows 'active'
        $this->actingAs($this->userWithRole('admin'))
            ->post('/risks', [
                'type' => 'workaround',
                'name' => 'Y',
                'description' => 'z',
                'owner_name' => 'A',
                'status' => 'active',
                'entry_date' => '2025-04-01',
            ])
            ->assertRedirect();
    }

    public function test_admin_can_update(): void
    {
        $entry = $this->makeEntry(['name' => 'Old']);

        $this->actingAs($this->userWithRole('admin'))
            ->put("/risks/{$entry->display_id}", [
                'type' => 'risk',
                'name' => 'New',
                'description' => 'd',
                'owner_name' => 'owner',
                'status' => 'mitigated',
                'entry_date' => '2025-04-01',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('risk_entries', ['id' => $entry->id, 'name' => 'New', 'status' => 'mitigated']);
    }

    public function test_admin_can_delete(): void
    {
        $entry = $this->makeEntry();

        $this->actingAs($this->userWithRole('admin'))
            ->delete("/risks/{$entry->display_id}")
            ->assertRedirect('/risks');

        $this->assertDatabaseMissing('risk_entries', ['id' => $entry->id]);
    }

    public function test_operator_cannot_delete(): void
    {
        $entry = $this->makeEntry();

        $this->actingAs($this->userWithRole('operator'))
            ->delete("/risks/{$entry->display_id}")
            ->assertForbidden();
    }

    public function test_viewer_cannot_create(): void
    {
        $this->actingAs($this->userWithRole('viewer'))
            ->post('/risks', [
                'type' => 'risk',
                'name' => 'X',
                'description' => 'y',
                'owner_name' => 'Z',
                'status' => 'open',
                'entry_date' => '2025-04-01',
            ])
            ->assertForbidden();
    }
}
