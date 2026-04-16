<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMeetingRequest;
use App\Http\Requests\UpdateMeetingRequest;
use App\Models\Meeting;
use App\Models\Unit;
use App\Support\PultEnums;
use App\Support\UnitContextScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MeetingController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Meeting::class);

        $baseQuery = Meeting::query()->with(['unit', 'creator']);
        UnitContextScope::apply($baseQuery);

        $queryBuilder = new QueryBuilder($baseQuery);
        $queryBuilder->allowedFilters(
            AllowedFilter::exact('type'),
            AllowedFilter::exact('status'),
            AllowedFilter::exact('unit_id'),
            AllowedFilter::partial('title'),
        );
        $queryBuilder->allowedSorts('title', 'scheduled_at', 'type', 'status', 'created_at');
        $queryBuilder->defaultSort('-scheduled_at');

        $meetings = $queryBuilder->paginate(20)->withQueryString();

        return Inertia::render('Meetings/Index', [
            'meetings' => $meetings,
            'allUnits' => Unit::orderBy('sort_order')->get(['id', 'name', 'color', 'unit_type']),
            'types' => PultEnums::meetingTypes(),
            'statuses' => PultEnums::meetingStatuses(),
            'filters' => request()->query('filter', []),
            'can' => [
                'create' => request()->user()?->can('create', Meeting::class),
                'delete' => request()->user()?->can('delete', new Meeting),
            ],
        ]);
    }

    public function store(StoreMeetingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()?->id;

        Meeting::create($data);

        return back()->with('flash.success', __('pult.meetings.flash.created'));
    }

    public function update(UpdateMeetingRequest $request, Meeting $meeting): RedirectResponse
    {
        $meeting->update($request->validated());

        return back()->with('flash.success', __('pult.meetings.flash.updated'));
    }

    public function destroy(Meeting $meeting): RedirectResponse
    {
        Gate::authorize('delete', $meeting);

        $meeting->delete();

        return back()->with('flash.success', __('pult.meetings.flash.deleted'));
    }
}
