<?php

namespace App\Http\Controllers;

use App\Models\MvrDailyEntry;
use App\Models\MvrEntry;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FinanceController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', MvrEntry::class);

        $year = (int) $request->query('year', (string) now()->year);
        $month = (int) $request->query('month', (string) now()->month);

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        $revenueUnits = Unit::query()
            ->where('unit_type', 'revenue')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'color']);
        $revenueUnitIds = $revenueUnits->pluck('id')->all();

        $activeUnitId = session('active_unit_id');
        $singleUnitView = $activeUnitId && in_array($activeUnitId, $revenueUnitIds, true);
        $scopeUnitIds = $singleUnitView ? [$activeUnitId] : $revenueUnitIds;

        // Daily entries for selected month — single unit shows raw rows; holding view aggregates by date.
        if ($singleUnitView) {
            $dailyEntries = MvrDailyEntry::query()
                ->where('unit_id', $activeUnitId)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->orderBy('date')
                ->get(['id', 'unit_id', 'date', 'plan', 'fact'])
                ->map(fn (MvrDailyEntry $d) => [
                    'id' => $d->id,
                    'unit_id' => $d->unit_id,
                    'date' => $d->date->toDateString(),
                    'plan' => (float) $d->plan,
                    'fact' => (float) $d->fact,
                ])
                ->all();
        } else {
            $dailyEntries = MvrDailyEntry::query()
                ->whereIn('unit_id', $revenueUnitIds)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->selectRaw('date, SUM(plan) as plan, SUM(fact) as fact')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn ($r) => [
                    'id' => null,
                    'unit_id' => null,
                    'date' => Carbon::parse($r->date)->toDateString(),
                    'plan' => (float) $r->plan,
                    'fact' => (float) $r->fact,
                ])
                ->all();
        }

        // Monthly plan entries (Plan tab) — keep per-unit, scoped same way.
        $entries = MvrEntry::query()
            ->whereIn('unit_id', $scopeUnitIds)
            ->where('year', $year)
            ->orderBy('month')
            ->orderBy('unit_id')
            ->get();

        // Monthly deviations — aggregate by month from daily rows in the scope.
        $monthlyDailyRows = MvrDailyEntry::query()
            ->whereIn('unit_id', $scopeUnitIds)
            ->whereYear('date', $year)
            ->selectRaw("strftime('%m', date) as m, SUM(plan) as plan_sum, SUM(fact) as fact_sum")
            ->groupBy(DB::raw("strftime('%m', date)"))
            ->get()
            ->keyBy(fn ($r) => (int) $r->m);

        $monthlyDeviations = [];
        for ($m = 1; $m <= 12; $m++) {
            $row = $monthlyDailyRows->get($m);
            $planSum = (float) ($row->plan_sum ?? 0);
            $factSum = (float) ($row->fact_sum ?? 0);
            $monthlyDeviations[] = [
                'month' => $m,
                'plan' => $planSum,
                'fact' => $factSum,
                'diff' => $factSum - $planSum,
                'diff_pct' => $planSum > 0 ? round(($factSum - $planSum) / $planSum * 100, 1) : 0.0,
                'has_data' => $planSum > 0 || $factSum > 0,
            ];
        }

        return Inertia::render('Finance/Index', [
            'entries' => $entries,
            'dailyEntries' => $dailyEntries,
            'monthlyDeviations' => $monthlyDeviations,
            'revenueUnits' => $revenueUnits,
            'year' => $year,
            'month' => $month,
            'daysInMonth' => $daysInMonth,
            'isHoldingView' => ! $singleUnitView,
            'scopeUnitId' => $singleUnitView ? $activeUnitId : null,
            'can' => [
                'create' => $request->user()?->can('create', MvrEntry::class),
                'delete' => $request->user()?->can('delete', new MvrEntry),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', MvrEntry::class);

        $revenueUnitIds = Unit::query()->where('unit_type', 'revenue')->pluck('id')->all();

        $validated = $request->validate([
            'unit_id' => ['required', 'string', 'in:'.implode(',', $revenueUnitIds)],
            'year' => ['required', 'integer', 'min:2020', 'max:2050'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'target' => ['required', 'numeric', 'min:0'],
            'actual' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ]);

        MvrEntry::updateOrCreate(
            [
                'unit_id' => $validated['unit_id'],
                'year' => $validated['year'],
                'month' => $validated['month'],
            ],
            [
                'target' => $validated['target'],
                'actual' => $validated['actual'] ?? 0,
                'currency' => $validated['currency'] ?? 'USD',
            ],
        );

        return back()->with('flash.success', __('pult.finance.flash.created'));
    }

    public function update(Request $request, MvrEntry $mvrEntry): RedirectResponse
    {
        Gate::authorize('update', $mvrEntry);

        $validated = $request->validate([
            'target' => ['required', 'numeric', 'min:0'],
            'actual' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $mvrEntry->update($validated);

        return back()->with('flash.success', __('pult.finance.flash.updated'));
    }

    public function destroy(MvrEntry $mvrEntry): RedirectResponse
    {
        Gate::authorize('delete', $mvrEntry);
        $mvrEntry->delete();

        return back()->with('flash.success', __('pult.finance.flash.deleted'));
    }

    // Daily entry CRUD
    public function storeDaily(Request $request): RedirectResponse
    {
        Gate::authorize('create', MvrEntry::class);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'plan' => ['required', 'numeric', 'min:0'],
            'fact' => ['required', 'numeric', 'min:0'],
        ]);

        MvrDailyEntry::updateOrCreate(
            ['date' => $validated['date']],
            $validated,
        );

        return back()->with('flash.success', __('pult.finance.flash.updated'));
    }

    public function updateDaily(Request $request, MvrDailyEntry $mvrDailyEntry): RedirectResponse
    {
        Gate::authorize('update', MvrEntry::first() ?? new MvrEntry);

        $validated = $request->validate([
            'plan' => ['required', 'numeric', 'min:0'],
            'fact' => ['required', 'numeric', 'min:0'],
        ]);

        $mvrDailyEntry->update($validated);

        return back()->with('flash.success', __('pult.finance.flash.updated'));
    }
}
