<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstructionRequest;
use App\Http\Requests\UpdateInstructionRequest;
use App\Models\Instruction;
use App\Models\Unit;
use App\Support\PultEnums;
use App\Support\UnitContextScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InstructionController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Instruction::class);

        $baseQuery = Instruction::query()->with(['unit', 'creator']);
        UnitContextScope::apply($baseQuery);

        $queryBuilder = new QueryBuilder($baseQuery);
        $queryBuilder->allowedFilters(
            AllowedFilter::exact('type'),
            AllowedFilter::exact('unit_id'),
            AllowedFilter::partial('title'),
        );
        $queryBuilder->allowedSorts('title', 'type', 'created_at');
        $queryBuilder->defaultSort('-created_at');

        $instructions = $queryBuilder->paginate(20)->withQueryString();

        return Inertia::render('Instructions/Index', [
            'instructions' => $instructions,
            'allUnits' => Unit::orderBy('sort_order')->get(['id', 'name', 'color', 'unit_type']),
            'types' => PultEnums::instructionTypes(),
            'filters' => request()->query('filter', []),
            'can' => [
                'create' => request()->user()?->can('create', Instruction::class),
                'delete' => request()->user()?->can('delete', new Instruction),
            ],
        ]);
    }

    public function store(StoreInstructionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()?->id;

        Instruction::create($data);

        return back()->with('flash.success', __('pult.instructions.flash.created'));
    }

    public function update(UpdateInstructionRequest $request, Instruction $instruction): RedirectResponse
    {
        $instruction->update($request->validated());

        return back()->with('flash.success', __('pult.instructions.flash.updated'));
    }

    public function destroy(Instruction $instruction): RedirectResponse
    {
        Gate::authorize('delete', $instruction);

        $instruction->delete();

        return back()->with('flash.success', __('pult.instructions.flash.deleted'));
    }
}
