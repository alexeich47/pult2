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

        $tab = request()->query('tab', 'active');
        $workStageFilter = request()->query('work_stage');

        $baseQuery = Employee::query()
            ->with(['unit', 'manager'])
            ->where('status', $tab);

        // If filtering by work_stage on the active tab
        if ($tab === 'active' && $workStageFilter) {
            $baseQuery->where('work_stage', $workStageFilter);
        }

        $queryBuilder = new QueryBuilder($baseQuery);
        $queryBuilder->allowedFilters(
            'unit_id',
            'department',
            AllowedFilter::partial('name'),
            AllowedFilter::partial('position'),
        );
        $queryBuilder->allowedSorts('name', 'position', 'department', 'created_at');
        $queryBuilder->defaultSort('id');

        $employees = $queryBuilder->paginate(20)->withQueryString();

        // Counts per tab (unfiltered)
        $counts = Employee::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        // Work stage counts (only for active employees)
        $stageCounts = Employee::query()
            ->where('status', 'active')
            ->selectRaw('work_stage, count(*) as count')
            ->groupBy('work_stage')
            ->pluck('count', 'work_stage')
            ->all();

        // Active employees for the manager select
        $managers = Employee::query()
            ->where('status', 'active')
            ->whereNotNull('name')
            ->orderBy('name')
            ->get(['id', 'name', 'position', 'unit_id']);

        return Inertia::render('Personnel/Index', [
            'employees' => $employees,
            'counts' => [
                'active' => $counts['active'] ?? 0,
                'vacancy' => $counts['vacancy'] ?? 0,
                'fired' => $counts['fired'] ?? 0,
            ],
            'stageCounts' => [
                'onboarding' => $stageCounts['onboarding'] ?? 0,
                'probation' => $stageCounts['probation'] ?? 0,
                'employee' => $stageCounts['employee'] ?? 0,
                'offboarding' => $stageCounts['offboarding'] ?? 0,
            ],
            'tab' => $tab,
            'workStageFilter' => $workStageFilter,
            'allUnits' => Unit::orderBy('sort_order')->get(),
            'managers' => $managers,
            'departments' => PultEnums::departments(),
            'statuses' => PultEnums::employeeStatuses(),
            'workStages' => PultEnums::workStages(),
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
