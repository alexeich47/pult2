<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRiskEntryRequest;
use App\Http\Requests\UpdateRiskEntryRequest;
use App\Models\Employee;
use App\Models\RiskEntry;
use App\Support\PultEnums;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class RiskEntryController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', RiskEntry::class);

        $entries = RiskEntry::query()
            ->orderBy('type')
            ->orderBy('entry_date', 'desc')
            ->get()
            ->groupBy('type');

        // Ensure every log type has a (possibly empty) group so the UI can render all 4 sections.
        $grouped = collect(PultEnums::riskTypes())
            ->mapWithKeys(fn (string $type) => [$type => $entries->get($type, collect())->values()])
            ->all();

        return Inertia::render('Risks/Index', [
            'entriesByType' => $grouped,
            'types' => PultEnums::riskTypes(),
            'statusesByType' => RiskEntry::STATUSES_BY_TYPE,
            'employees' => Employee::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'position', 'unit_id']),
            'can' => [
                'create' => request()->user()?->can('create', RiskEntry::class),
                'delete' => request()->user()?->can('delete', new RiskEntry),
            ],
        ]);
    }

    public function show(RiskEntry $riskEntry): Response
    {
        Gate::authorize('view', $riskEntry);

        return Inertia::render('Risks/Show', [
            'entry' => $riskEntry,
            'statuses' => RiskEntry::STATUSES_BY_TYPE[$riskEntry->type],
            'employees' => Employee::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'position', 'unit_id']),
            'can' => [
                'update' => request()->user()?->can('update', $riskEntry),
                'delete' => request()->user()?->can('delete', $riskEntry),
            ],
        ]);
    }

    public function store(StoreRiskEntryRequest $request): RedirectResponse
    {
        $entry = RiskEntry::create($request->validated());

        return redirect()
            ->route('risks.show', $entry)
            ->with('flash.success', __('pult.risks.flash.created'));
    }

    public function update(UpdateRiskEntryRequest $request, RiskEntry $riskEntry): RedirectResponse
    {
        $riskEntry->update($request->validated());

        return back()->with('flash.success', __('pult.risks.flash.updated'));
    }

    public function destroy(RiskEntry $riskEntry): RedirectResponse
    {
        Gate::authorize('delete', $riskEntry);

        $riskEntry->delete();

        return redirect()
            ->route('risks.index')
            ->with('flash.success', __('pult.risks.flash.deleted'));
    }
}
