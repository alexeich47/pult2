<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProcessRequest;
use App\Http\Requests\UpdateProcessRequest;
use App\Models\Employee;
use App\Models\Process;
use App\Models\Unit;
use App\Support\PultEnums;
use App\Support\UnitContextScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProcessController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Process::class);

        $baseQuery = Process::query()->with(['unit', 'owner']);
        UnitContextScope::apply($baseQuery);

        $queryBuilder = new QueryBuilder($baseQuery);
        $queryBuilder->allowedFilters(
            AllowedFilter::exact('unit_id'),
            AllowedFilter::exact('owner_id'),
            AllowedFilter::exact('category'),
            AllowedFilter::exact('maturity'),
            AllowedFilter::partial('title'),
        );
        $queryBuilder->allowedSorts('title', 'category', 'maturity', 'sort_order', 'created_at');
        $queryBuilder->defaultSort('sort_order');

        return Inertia::render('Processes/Index', [
            'processes' => $queryBuilder->paginate(20)->withQueryString(),
            'allUnits' => Unit::orderBy('sort_order')->get(['id', 'name', 'color', 'unit_type']),
            'employees' => Employee::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'position', 'unit_id']),
            'categories' => PultEnums::processCategories(),
            'maturityLevels' => PultEnums::processMaturityLevels(),
            'filters' => request()->query('filter', []),
            'can' => [
                'create' => request()->user()?->can('create', Process::class),
                'delete' => request()->user()?->can('delete', new Process),
            ],
        ]);
    }

    public function store(StoreProcessRequest $request): RedirectResponse
    {
        Process::create($request->validated());

        return back()->with('flash.success', __('pult.processes.flash.created'));
    }

    public function update(UpdateProcessRequest $request, Process $process): RedirectResponse
    {
        $process->update($request->validated());

        return back()->with('flash.success', __('pult.processes.flash.updated'));
    }

    public function bulkDestroy(): RedirectResponse
    {
        Gate::authorize('delete', new Process);

        /** @var array<int, int> $ids */
        $ids = request()->input('ids', []);
        abort_if(empty($ids), 422);

        Process::whereIn('id', $ids)->delete();

        return back()->with('flash.success', __('pult.processes.flash.deleted'));
    }

    public function destroy(Process $process): RedirectResponse
    {
        Gate::authorize('delete', $process);

        $process->delete();

        return back()->with('flash.success', __('pult.processes.flash.deleted'));
    }
}
