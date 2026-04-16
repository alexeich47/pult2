<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Employee;
use App\Models\Idea;
use App\Models\Service;
use App\Models\Unit;
use App\Support\PultEnums;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class StructureController extends Controller
{
    public function index(): Response
    {
        $units = Unit::query()
            ->withCount('employees')
            ->with(['children', 'head', 'deputy'])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Structure/Index', [
            'units' => $units,
            'employees' => Employee::where('status', 'active')
                ->whereNotNull('name')
                ->orderBy('name')
                ->get(['id', 'name', 'position', 'unit_id']),
            'stages' => PultEnums::unitStages(),
        ]);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return back()->with('flash.success', __('pult.structure.flash.created'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return back()->with('flash.success', __('pult.structure.flash.updated'));
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        Gate::authorize('delete', $unit);

        // Prevent deletion if unit has related data
        $hasEmployees = $unit->employees()->exists();
        $hasIdeas = Idea::where('unit_id', $unit->id)->exists();
        $hasServices = Service::where('unit_id', $unit->id)->exists();
        $hasChildren = $unit->children()->exists();

        if ($hasEmployees || $hasIdeas || $hasServices || $hasChildren) {
            return back()->with('flash.error', __('pult.structure.delete_blocked'));
        }

        $unit->delete();

        return back()->with('flash.success', __('pult.structure.flash.deleted'));
    }
}
