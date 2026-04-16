<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRndProjectRequest;
use App\Http\Requests\UpdateRndProjectRequest;
use App\Models\Employee;
use App\Models\RndProject;
use App\Models\Unit;
use App\Support\PultEnums;
use App\Support\UnitContextScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RndProjectController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', RndProject::class);

        $baseQuery = RndProject::query()->with(['unit', 'owner']);
        UnitContextScope::apply($baseQuery);

        $queryBuilder = new QueryBuilder($baseQuery);
        $queryBuilder->allowedFilters(
            AllowedFilter::exact('unit_id'),
            AllowedFilter::exact('owner_id'),
            AllowedFilter::exact('status'),
            AllowedFilter::exact('priority'),
            AllowedFilter::partial('title'),
        );
        $queryBuilder->allowedSorts('title', 'priority', 'status', 'budget', 'deadline', 'created_at');
        $queryBuilder->defaultSort('-created_at');

        return Inertia::render('Rnd/Index', [
            'projects' => $queryBuilder->paginate(20)->withQueryString(),
            'allUnits' => Unit::orderBy('sort_order')->get(['id', 'name', 'color', 'unit_type']),
            'employees' => Employee::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'position', 'unit_id']),
            'statuses' => PultEnums::rndStatuses(),
            'priorities' => PultEnums::rndPriorities(),
            'currencies' => PultEnums::serviceCurrencies(),
            'filters' => request()->query('filter', []),
            'can' => [
                'create' => request()->user()?->can('create', RndProject::class),
                'delete' => request()->user()?->can('delete', new RndProject),
            ],
        ]);
    }

    public function store(StoreRndProjectRequest $request): RedirectResponse
    {
        RndProject::create($request->validated());

        return back()->with('flash.success', __('pult.rnd.flash.created'));
    }

    public function update(UpdateRndProjectRequest $request, RndProject $rndProject): RedirectResponse
    {
        $rndProject->update($request->validated());

        return back()->with('flash.success', __('pult.rnd.flash.updated'));
    }

    public function bulkDestroy(): RedirectResponse
    {
        Gate::authorize('delete', new RndProject);

        /** @var array<int, int> $ids */
        $ids = request()->input('ids', []);
        abort_if(empty($ids), 422);

        RndProject::whereIn('id', $ids)->delete();

        return back()->with('flash.success', __('pult.rnd.flash.deleted'));
    }

    public function destroy(RndProject $rndProject): RedirectResponse
    {
        Gate::authorize('delete', $rndProject);

        $rndProject->delete();

        return back()->with('flash.success', __('pult.rnd.flash.deleted'));
    }
}
