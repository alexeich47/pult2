<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaRequest;
use App\Http\Requests\UpdateIdeaRequest;
use App\Models\Employee;
use App\Models\Idea;
use App\Models\Unit;
use App\Support\PultEnums;
use App\Support\UnitContextScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class IdeaController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Idea::class);

        $baseQuery = Idea::query()->with(['unit', 'author']);
        UnitContextScope::apply($baseQuery);

        $queryBuilder = new QueryBuilder($baseQuery);
        $queryBuilder->allowedFilters(
            AllowedFilter::exact('unit_id'),
            AllowedFilter::exact('author_id'),
            AllowedFilter::exact('status'),
            AllowedFilter::exact('priority'),
            AllowedFilter::partial('title'),
        );
        $queryBuilder->allowedSorts(
            'display_id',
            'unit_id',
            'title',
            'status',
            'author_id',
            'priority',
            'created_at',
            AllowedSort::field('id'),
        );
        $queryBuilder->defaultSort('-created_at');

        return Inertia::render('Ideas/Index', [
            'ideas' => $queryBuilder->paginate(20)->withQueryString(),
            'allUnits' => Unit::orderBy('sort_order')->get(['id', 'name', 'color', 'unit_type']),
            'authors' => Employee::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'position', 'unit_id']),
            'statuses' => PultEnums::ideaStatuses(),
            'priorities' => PultEnums::ideaPriorities(),
            'operators' => PultEnums::ideaFilterOperators(),
            'filters' => request()->query('filter', []),
            'sort' => request()->query('sort', '-created_at'),
            'can' => [
                'create' => request()->user()?->can('create', Idea::class),
                'delete' => request()->user()?->can('delete', new Idea),
            ],
        ]);
    }

    public function show(Idea $idea): Response
    {
        Gate::authorize('view', $idea);

        $idea->load(['unit', 'author.unit']);

        return Inertia::render('Ideas/Show', [
            'idea' => $idea,
            'allUnits' => Unit::orderBy('sort_order')->get(['id', 'name', 'color', 'unit_type']),
            'authors' => Employee::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'position', 'unit_id']),
            'statuses' => PultEnums::ideaStatuses(),
            'priorities' => PultEnums::ideaPriorities(),
            'can' => [
                'update' => request()->user()?->can('update', $idea),
                'delete' => request()->user()?->can('delete', $idea),
            ],
        ]);
    }

    public function store(StoreIdeaRequest $request): RedirectResponse
    {
        $idea = Idea::create($request->validated());

        return redirect()
            ->route('ideas.show', $idea)
            ->with('flash.success', __('pult.ideas.flash.created'));
    }

    /**
     * Full update from form modal.
     */
    public function update(UpdateIdeaRequest $request, Idea $idea): RedirectResponse
    {
        $idea->update($request->validated());

        return back()->with('flash.success', __('pult.ideas.flash.updated'));
    }

    /**
     * Inline single-field patch from table cell click.
     */
    public function patch(Idea $idea): RedirectResponse
    {
        Gate::authorize('update', $idea);

        $field = request()->input('field');
        $value = request()->input('value');

        $allowed = ['unit_id', 'author_id', 'status', 'priority'];
        abort_unless(in_array($field, $allowed, true), 422, 'Field not patchable');

        // Validate the value per field
        $rules = match ($field) {
            'unit_id' => ['exists:units,id'],
            'author_id' => ['integer', 'exists:employees,id'],
            'status' => [Rule::in(PultEnums::ideaStatuses())],
            'priority' => [Rule::in(PultEnums::ideaPriorities())],
        };

        request()->validate(['value' => ['required', ...$rules]]);

        $idea->update([$field => $value]);

        return back();
    }

    public function bulkDestroy(): RedirectResponse
    {
        Gate::authorize('delete', new Idea);

        /** @var array<int, int> $ids */
        $ids = request()->input('ids', []);
        abort_if(empty($ids), 422);

        Idea::whereIn('id', $ids)->delete();

        return back()->with('flash.success', __('pult.ideas.flash.deleted'));
    }

    public function destroy(Idea $idea): RedirectResponse
    {
        Gate::authorize('delete', $idea);

        $idea->delete();

        return redirect()
            ->route('ideas.index')
            ->with('flash.success', __('pult.ideas.flash.deleted'));
    }
}
