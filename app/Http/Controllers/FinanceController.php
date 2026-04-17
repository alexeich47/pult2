<?php

namespace App\Http\Controllers;

use App\Models\MvrDailyEntry;
use App\Models\MvrEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        // Monthly plan entries
        $entries = MvrEntry::query()
            ->whereNull('unit_id')
            ->where('year', $year)
            ->orderBy('month')
            ->get();

        // Daily entries for selected month
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $dailyEntries = MvrDailyEntry::query()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date')
            ->get();

        // Monthly target for the daily goal reference line
        $currentMonthPlan = $entries->firstWhere('month', $month);
        $daysInMonth = $startOfMonth->daysInMonth;
        $dailyGoal = $currentMonthPlan ? round((float) $currentMonthPlan->target / $daysInMonth, 2) : 0;

        return Inertia::render('Finance/Index', [
            'entries' => $entries,
            'dailyEntries' => $dailyEntries,
            'year' => $year,
            'month' => $month,
            'dailyGoal' => $dailyGoal,
            'daysInMonth' => $daysInMonth,
            'can' => [
                'create' => $request->user()?->can('create', MvrEntry::class),
                'delete' => $request->user()?->can('delete', new MvrEntry),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', MvrEntry::class);

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2050'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'target' => ['required', 'numeric', 'min:0'],
            'actual' => ['required', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ]);

        MvrEntry::create(array_merge(['unit_id' => null], $validated));

        return back()->with('flash.success', __('pult.finance.flash.created'));
    }

    public function update(Request $request, MvrEntry $mvrEntry): RedirectResponse
    {
        Gate::authorize('update', $mvrEntry);

        $validated = $request->validate([
            'target' => ['required', 'numeric', 'min:0'],
            'actual' => ['required', 'numeric', 'min:0'],
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
