<?php

namespace App\Http\Controllers;

use App\Models\MvrEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FinanceController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', MvrEntry::class);

        $year = (int) $request->query('year', (string) now()->year);

        $entries = MvrEntry::query()
            ->whereNull('unit_id')
            ->where('year', $year)
            ->orderBy('month')
            ->get();

        return Inertia::render('Finance/Index', [
            'entries' => $entries,
            'year' => $year,
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
}
