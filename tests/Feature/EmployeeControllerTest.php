<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
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

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/personnel')
            ->assertRedirect('/login');
    }

    public function test_admin_can_list_employees(): void
    {
        Employee::create([
            'unit_id' => 'holding',
            'name' => 'Test Person',
            'position' => 'CTO',
            'department' => 'Технологии',
            'email' => 'test@example.com',
            'telegram' => '@test',
            'status' => 'active',
        ]);

        $this->actingAs($this->userWithRole('admin'))
            ->get('/personnel')
            ->assertInertia(fn ($page) => $page
                ->component('Personnel/Index')
                ->has('employees.data', 1)
                ->has('allUnits', 9)
                ->has('departments', 9)
                ->where('can.create', true)
                ->where('can.delete', true)
            );
    }

    public function test_operator_can_create_but_not_delete(): void
    {
        $this->actingAs($this->userWithRole('operator'))
            ->get('/personnel')
            ->assertInertia(fn ($page) => $page
                ->where('can.create', true)
                ->where('can.delete', false)
            );
    }

    public function test_viewer_can_read_only(): void
    {
        $this->actingAs($this->userWithRole('viewer'))
            ->get('/personnel')
            ->assertInertia(fn ($page) => $page
                ->where('can.create', false)
                ->where('can.delete', false)
            );
    }

    public function test_admin_can_store_active_employee(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->post('/personnel', [
                'unit_id' => 'playduck',
                'name' => 'Нина Петрова',
                'position' => 'QA Lead',
                'department' => 'Технологии',
                'email' => 'nina@playduck.com',
                'telegram' => '@nina',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'name' => 'Нина Петрова',
            'unit_id' => 'playduck',
        ]);
    }

    public function test_vacancy_store_clears_name_and_contact(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->post('/personnel', [
                'unit_id' => 'citadel',
                'name' => 'Ignored Name',
                'position' => 'CMO',
                'department' => 'Маркетинг',
                'email' => 'ignored@example.com',
                'telegram' => '@ignored',
                'status' => 'vacancy',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'position' => 'CMO',
            'status' => 'vacancy',
            'name' => null,
            'email' => null,
            'telegram' => null,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post('/personnel', [
                'unit_id' => 'nonexistent',
                'position' => '',
                'department' => 'NotADepartment',
                'status' => 'active',
            ])
            ->assertSessionHasErrors(['unit_id', 'position', 'department', 'name']);
    }

    public function test_viewer_cannot_store(): void
    {
        $this->actingAs($this->userWithRole('viewer'))
            ->post('/personnel', [
                'unit_id' => 'holding',
                'name' => 'Should Fail',
                'position' => 'Dev',
                'department' => 'Технологии',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_update(): void
    {
        $employee = Employee::create([
            'unit_id' => 'holding',
            'name' => 'Old Name',
            'position' => 'Dev',
            'department' => 'Технологии',
            'status' => 'active',
        ]);

        $this->actingAs($this->userWithRole('admin'))
            ->put("/personnel/{$employee->id}", [
                'unit_id' => 'holding',
                'name' => 'New Name',
                'position' => 'Dev',
                'department' => 'Технологии',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'New Name',
        ]);
    }

    public function test_admin_can_delete(): void
    {
        $employee = Employee::create([
            'unit_id' => 'holding',
            'name' => 'To Delete',
            'position' => 'Dev',
            'department' => 'Технологии',
            'status' => 'active',
        ]);

        $this->actingAs($this->userWithRole('admin'))
            ->delete("/personnel/{$employee->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    public function test_operator_cannot_delete(): void
    {
        $employee = Employee::create([
            'unit_id' => 'holding',
            'name' => 'Protected',
            'position' => 'Dev',
            'department' => 'Технологии',
            'status' => 'active',
        ]);

        $this->actingAs($this->userWithRole('operator'))
            ->delete("/personnel/{$employee->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('employees', ['id' => $employee->id]);
    }

    public function test_employee_changes_are_written_to_activity_log(): void
    {
        $employee = Employee::create([
            'unit_id' => 'holding',
            'name' => 'Activity Test',
            'position' => 'Dev',
            'department' => 'Технологии',
            'status' => 'active',
        ]);

        $employee->update(['position' => 'Senior Dev']);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'employee',
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
            'description' => 'employee.updated',
        ]);
    }

    public function test_unit_filter_narrows_results(): void
    {
        Employee::create(['unit_id' => 'holding', 'name' => 'A', 'position' => 'X', 'department' => 'HR', 'status' => 'active']);
        Employee::create(['unit_id' => 'playduck', 'name' => 'B', 'position' => 'X', 'department' => 'HR', 'status' => 'active']);

        $this->actingAs($this->userWithRole('admin'))
            ->get('/personnel?filter[unit_id]=playduck')
            ->assertInertia(fn ($page) => $page
                ->has('employees.data', 1)
                ->where('employees.data.0.name', 'B')
            );
    }
}
