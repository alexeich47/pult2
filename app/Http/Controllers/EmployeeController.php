<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\Unit;
use App\Support\PultEnums;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Employee::class);

        $queryBuilder = new QueryBuilder(Employee::query()->with('unit'));
        $queryBuilder->allowedFilters(
            'unit_id',
            'status',
            'department',
            AllowedFilter::partial('name'),
            AllowedFilter::partial('position'),
        );
        $queryBuilder->allowedSorts('name', 'position', 'department', 'status', 'created_at');
        $queryBuilder->defaultSort('id');

        $employees = $queryBuilder->paginate(20)->withQueryString();

        return Inertia::render('Personnel/Index', [
            'employees' => $employees,
            'allUnits' => Unit::orderBy('sort_order')->get(),
            'departments' => PultEnums::departments(),
            'statuses' => PultEnums::employeeStatuses(),
            'filters' => request()->query('filter', []),
            'can' => [
                'create' => request()->user()?->can('create', Employee::class),
                'delete' => request()->user()?->can('delete', new Employee),
            ],
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        Employee::create($request->validated());

        return back()->with('flash.success', __('pult.personnel.flash.created'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        return back()->with('flash.success', __('pult.personnel.flash.updated'));
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        Gate::authorize('delete', $employee);

        $employee->delete();

        return back()->with('flash.success', __('pult.personnel.flash.deleted'));
    }
}
