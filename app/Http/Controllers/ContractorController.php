<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\Unit;
use App\Support\UnitContextScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContractorController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Contractor::query()->with('unit');
        UnitContextScope::apply($query);
        $contractors = $query->orderByDesc('created_at')->get();

        return Inertia::render('Contractors/Index', [
            'contractors' => $contractors,
            'allUnits' => Unit::orderBy('sort_order')->get(['id', 'name', 'color', 'unit_type']),
            'statuses' => ['active', 'paused', 'ended'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateInput($request);
        Contractor::create($validated);

        return back()->with('flash.success', 'Контрактор добавлен');
    }

    public function update(Request $request, Contractor $contractor): RedirectResponse
    {
        $validated = $this->validateInput($request);
        $contractor->update($validated);

        return back()->with('flash.success', 'Обновлено');
    }

    public function destroy(Contractor $contractor): RedirectResponse
    {
        $contractor->delete();

        return back()->with('flash.success', 'Удалено');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateInput(Request $request): array
    {
        return $request->validate([
            'unit_id' => ['nullable', 'string', 'exists:units,id'],
            'name' => ['required', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:active,paused,ended'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'rate' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
