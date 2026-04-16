<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\User;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IdeaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RolesAndPermissionsSeeder::class,
            UnitSeeder::class,
            EmployeeSeeder::class,
        ]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }

    private function makeIdea(array $attrs = []): Idea
    {
        return Idea::create(array_merge([
            'unit_id' => 'swiftpunk',
            'author_id' => 1,
            'priority' => 'high',
            'status' => 'new',
            'title' => 'Sample idea',
            'description' => 'desc',
            'impact' => 'impact',
        ], $attrs));
    }

    public function test_guests_are_redirected(): void
    {
        $this->get('/ideas')->assertRedirect('/login');
    }

    public function test_admin_can_list(): void
    {
        $this->makeIdea(['title' => 'One']);
        $this->makeIdea(['title' => 'Two']);

        $this->actingAs($this->userWithRole('admin'))
            ->get('/ideas')
            ->assertInertia(fn ($page) => $page
                ->component('Ideas/Index')
                ->has('ideas.data', 2)
                ->has('allUnits', 12)
                ->has('statuses', 6)
                ->has('priorities', 3)
                ->where('can.create', true)
                ->where('can.delete', true)
            );
    }

    public function test_viewer_cannot_create(): void
    {
        $this->actingAs($this->userWithRole('viewer'))
            ->get('/ideas')
            ->assertInertia(fn ($page) => $page
                ->where('can.create', false)
                ->where('can.delete', false)
            );
    }

    public function test_display_id_is_generated_on_create(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post('/ideas', [
                'unit_id' => 'playduck',
                'author_id' => 1,
                'priority' => 'medium',
                'status' => 'new',
                'title' => 'Fresh',
                'description' => 'd',
                'impact' => 'i',
            ])
            ->assertRedirect();

        $idea = Idea::latest('id')->first();
        $this->assertNotNull($idea);
        $this->assertMatchesRegularExpression('/^ID-\d{3}$/', $idea->display_id);
    }

    public function test_store_validates_enum_fields(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post('/ideas', [
                'unit_id' => 'nonexistent',
                'author_id' => 9999,
                'priority' => 'urgent',
                'status' => 'wip',
                'title' => '',
                'description' => '',
                'impact' => '',
            ])
            ->assertSessionHasErrors(['unit_id', 'author_id', 'priority', 'status', 'title', 'description', 'impact']);
    }

    public function test_viewer_cannot_store(): void
    {
        $this->actingAs($this->userWithRole('viewer'))
            ->post('/ideas', [
                'unit_id' => 'swiftpunk',
                'author_id' => 1,
                'priority' => 'high',
                'status' => 'new',
                'title' => 'Denied',
                'description' => 'x',
                'impact' => 'x',
            ])
            ->assertForbidden();
    }

    public function test_show_renders_detail(): void
    {
        $idea = $this->makeIdea(['title' => 'Detail test']);

        $this->actingAs($this->userWithRole('viewer'))
            ->get("/ideas/{$idea->display_id}")
            ->assertInertia(fn ($page) => $page
                ->component('Ideas/Show')
                ->where('idea.display_id', $idea->display_id)
                ->where('idea.title', 'Detail test')
                ->has('idea.unit')
                ->has('idea.author')
                ->has('allUnits', 12)
            );
    }

    public function test_admin_can_update(): void
    {
        $idea = $this->makeIdea(['title' => 'Old title']);

        $this->actingAs($this->userWithRole('admin'))
            ->put("/ideas/{$idea->display_id}", [
                'unit_id' => 'swiftpunk',
                'author_id' => 1,
                'priority' => 'high',
                'status' => 'approved',
                'title' => 'New title',
                'description' => 'd',
                'impact' => 'i',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'title' => 'New title',
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_delete(): void
    {
        $idea = $this->makeIdea();

        $this->actingAs($this->userWithRole('admin'))
            ->delete("/ideas/{$idea->display_id}")
            ->assertRedirect('/ideas');

        $this->assertSoftDeleted('ideas', ['id' => $idea->id]);
    }

    public function test_operator_cannot_delete(): void
    {
        $idea = $this->makeIdea();

        $this->actingAs($this->userWithRole('operator'))
            ->delete("/ideas/{$idea->display_id}")
            ->assertForbidden();
    }

    public function test_status_filter_narrows_results(): void
    {
        $this->makeIdea(['title' => 'A', 'status' => 'new']);
        $this->makeIdea(['title' => 'B', 'status' => 'approved']);
        $this->makeIdea(['title' => 'C', 'status' => 'approved']);

        $this->actingAs($this->userWithRole('admin'))
            ->get('/ideas?filter[status]=approved')
            ->assertInertia(fn ($page) => $page
                ->has('ideas.data', 2)
            );
    }

    public function test_title_filter_uses_partial_match(): void
    {
        $this->makeIdea(['title' => 'Automated parsing']);
        $this->makeIdea(['title' => 'Manual review']);

        $this->actingAs($this->userWithRole('admin'))
            ->get('/ideas?filter[title]=auto')
            ->assertInertia(fn ($page) => $page
                ->has('ideas.data', 1)
                ->where('ideas.data.0.title', 'Automated parsing')
            );
    }

    public function test_sort_by_created_at_desc_is_default(): void
    {
        $oldIdea = $this->makeIdea(['title' => 'Old']);
        $oldIdea->created_at = Carbon::parse('2025-01-01');
        $oldIdea->saveQuietly();

        $newIdea = $this->makeIdea(['title' => 'New']);
        $newIdea->created_at = Carbon::parse('2025-12-31');
        $newIdea->saveQuietly();

        $this->actingAs($this->userWithRole('admin'))
            ->get('/ideas')
            ->assertInertia(fn ($page) => $page
                ->where('ideas.data.0.title', 'New')
                ->where('ideas.data.1.title', 'Old')
            );
    }

    public function test_activity_log_records_idea_updates(): void
    {
        $idea = $this->makeIdea();
        $idea->update(['title' => 'Updated']);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'idea',
            'subject_type' => Idea::class,
            'subject_id' => $idea->id,
            'description' => 'idea.updated',
        ]);
    }

    public function test_route_key_is_display_id(): void
    {
        $idea = $this->makeIdea();

        $this->assertSame('display_id', $idea->getRouteKeyName());

        // hitting with numeric id should 404 (display_id is the route key)
        $this->actingAs($this->userWithRole('admin'))
            ->get("/ideas/{$idea->id}")
            ->assertNotFound();

        $this->actingAs($this->userWithRole('admin'))
            ->get("/ideas/{$idea->display_id}")
            ->assertOk();
    }
}
