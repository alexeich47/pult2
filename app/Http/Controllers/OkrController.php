<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOkrEntryRequest;
use App\Http\Requests\UpdateOkrEntryRequest;
use App\Models\OkrEntry;
use App\Models\Unit;
use App\Support\PultEnums;
use App\Support\UnitContextScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OkrController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', OkrEntry::class);

        $baseQuery = OkrEntry::query()
            ->with(['unit', 'children'])
            ->whereNull('parent_id');
        UnitContextScope::apply($baseQuery);

        $queryBuilder = new QueryBuilder($baseQuery);
        $queryBuilder->allowedFilters(
            AllowedFilter::exact('type'),
            AllowedFilter::exact('status'),
            AllowedFilter::exact('unit_id'),
            AllowedFilter::partial('title'),
        );
        $queryBuilder->allowedSorts('title', 'progress', 'period', 'sort_order', 'created_at');
        $queryBuilder->defaultSort('sort_order');

        $entries = $queryBuilder->paginate(20)->withQueryString();

        return Inertia::render('Okr/Index', [
            'entries' => $entries,
            'allUnits' => Unit::orderBy('sort_order')->get(['id', 'name', 'color', 'unit_type']),
            'types' => PultEnums::okrTypes(),
            'statuses' => PultEnums::okrStatuses(),
            'filters' => request()->query('filter', []),
            'can' => [
                'create' => request()->user()?->can('create', OkrEntry::class),
                'delete' => request()->user()?->can('delete', new OkrEntry),
            ],
        ]);
    }

    public function store(StoreOkrEntryRequest $request): RedirectResponse
    {
        OkrEntry::create($request->validated());

        return back()->with('flash.success', __('pult.okr.flash.created'));
    }

    public function update(UpdateOkrEntryRequest $request, OkrEntry $okr): RedirectResponse
    {
        $okr->update($request->validated());

        return back()->with('flash.success', __('pult.okr.flash.updated'));
    }

    public function destroy(OkrEntry $okr): RedirectResponse
    {
        Gate::authorize('delete', $okr);

        $okr->delete();

        return back()->with('flash.success', __('pult.okr.flash.deleted'));
    }
}
