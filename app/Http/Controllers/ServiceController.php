<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Models\Unit;
use App\Support\PultEnums;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ServiceController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Service::class);

        $queryBuilder = new QueryBuilder(Service::query()->with('unit'));
        $queryBuilder->allowedFilters(
            AllowedFilter::exact('status'),
            AllowedFilter::exact('billing'),
            AllowedFilter::exact('unit_id'),
            AllowedFilter::exact('category'),
            AllowedFilter::partial('name'),
        );
        $queryBuilder->allowedSorts('name', 'cost', 'next_payment', 'status', 'billing', 'created_at');
        $queryBuilder->defaultSort('name');

        $services = $queryBuilder->get();

        // Totals across *all* services (ignoring query filter) — to match the legacy
        // header which always shows aggregate stats.
        $all = Service::all();
        $totals = [
            'total' => $all->count(),
            'active' => $all->where('status', 'active')->count(),
            'subs' => $all->whereIn('billing', ['monthly', 'yearly'])->count(),
            'monthly_total' => round($all->sum(fn (Service $s) => $s->monthlyCost()), 2),
        ];

        return Inertia::render('Services/Index', [
            'services' => $services,
            'allUnits' => Unit::orderBy('sort_order')->get(['id', 'name', 'color', 'unit_type']),
            'categories' => PultEnums::serviceCategories(),
            'currencies' => PultEnums::serviceCurrencies(),
            'billingCycles' => PultEnums::serviceBillingCycles(),
            'statuses' => PultEnums::serviceStatuses(),
            'totals' => $totals,
            'filters' => request()->query('filter', []),
            'can' => [
                'create' => request()->user()?->can('create', Service::class),
                'delete' => request()->user()?->can('delete', new Service),
            ],
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        Service::create($request->validated());

        return back()->with('flash.success', __('pult.services.flash.created'));
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return back()->with('flash.success', __('pult.services.flash.updated'));
    }

    public function destroy(Service $service): RedirectResponse
    {
        Gate::authorize('delete', $service);

        $service->delete();

        return back()->with('flash.success', __('pult.services.flash.deleted'));
    }
}
