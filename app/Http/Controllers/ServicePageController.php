<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MvrDailyEntry;
use App\Models\MvrEntry;
use App\Models\ServicePageAccess;
use App\Models\Unit;
use App\Models\User;
use App\Support\ServicePages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ServicePageController extends Controller
{
    public function index(Request $request): Response
    {
        $pages = ServicePages::all();

        // Resolve employee_ids → name + position for the access column.
        $employeeIds = collect($pages)->flatMap(fn ($p) => $p['employee_ids'])->unique()->values()->all();
        $employees = Employee::query()
            ->whereIn('id', $employeeIds)
            ->get(['id', 'name', 'position', 'email', 'unit_id'])
            ->keyBy('id');

        $payload = array_map(fn (array $page) => $page + [
            'allowed_employees' => array_values(array_filter(array_map(
                fn (int $id) => $employees->get($id)?->only(['id', 'name', 'position', 'email']),
                $page['employee_ids'],
            ))),
        ], $pages);

        $canEditAccess = $request->user()?->hasRole('super-admin') ?? false;

        return Inertia::render('ServicePages/Index', [
            'pages' => $payload,
            'canEditAccess' => $canEditAccess,
            'allEmployees' => $canEditAccess
                ? Employee::query()
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->where('status', 'active')
                    ->orderBy('unit_id')
                    ->orderBy('name')
                    ->get(['id', 'name', 'position', 'email', 'unit_id'])
                : [],
        ]);
    }

    public function updateAccess(Request $request, string $slug): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('super-admin'), 403);
        abort_if(ServicePages::find($slug) === null, 404);

        $validated = $request->validate([
            'employee_ids' => ['present', 'array'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
        ]);

        ServicePageAccess::where('page_slug', $slug)->delete();
        foreach (array_unique($validated['employee_ids']) as $id) {
            ServicePageAccess::create([
                'page_slug' => $slug,
                'employee_id' => (int) $id,
            ]);
        }

        return back()->with('flash.success', __('service_pages.access.flash_saved'));
    }

    public function daily(Request $request, string $unitSlug): Response
    {
        $page = $this->resolvePage($unitSlug);
        $this->authorizePage($request, $page);

        $unit = Unit::findOrFail($page['unit_id']);
        $yesterday = Carbon::yesterday()->toDateString();

        $recent = MvrDailyEntry::query()
            ->where('unit_id', $unit->id)
            ->orderByDesc('date')
            ->limit(14)
            ->get(['id', 'date', 'plan', 'fact'])
            ->map(fn (MvrDailyEntry $e) => [
                'id' => $e->id,
                'date' => $e->date->toDateString(),
                'plan' => (float) $e->plan,
                'fact' => (float) $e->fact,
            ]);

        $existing = MvrDailyEntry::query()
            ->where('unit_id', $unit->id)
            ->whereDate('date', $yesterday)
            ->first();

        return Inertia::render('ServicePages/DailyUnit', [
            'unitId' => $unit->id,
            'unitName' => $unit->name,
            'unitColor' => $unit->color,
            'pageTitle' => $page['title'],
            'icon' => $page['icon'],
            'defaultDate' => $yesterday,
            'defaultPlan' => 10000,
            'existingFact' => $existing ? (float) $existing->fact : 0,
            'recent' => $recent,
        ]);
    }

    public function storeDaily(Request $request, string $unitSlug): RedirectResponse
    {
        $page = $this->resolvePage($unitSlug);
        $this->authorizePage($request, $page);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'fact' => ['required', 'numeric', 'min:0'],
            'plan' => ['sometimes', 'numeric', 'min:0'],
        ]);

        MvrDailyEntry::updateOrCreate(
            ['date' => $validated['date'], 'unit_id' => $page['unit_id']],
            [
                'plan' => $validated['plan'] ?? 10000,
                'fact' => $validated['fact'],
            ],
        );

        return back()->with('flash.success', __('service_pages.daily.flash_saved'));
    }

    public function mvrPlanning(Request $request): Response
    {
        $page = ServicePages::find('mvr-planning');
        abort_if($page === null, 404);
        $this->authorizePage($request, $page);

        $year = (int) $request->query('year', (string) now()->year);

        $revenueUnits = Unit::query()
            ->where('unit_type', 'revenue')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'color']);

        // Pre-load existing monthly targets for the year, indexed by unit+month for the frontend.
        $existing = MvrEntry::query()
            ->whereIn('unit_id', $revenueUnits->pluck('id'))
            ->where('year', $year)
            ->get(['unit_id', 'month', 'target']);

        $targets = [];
        foreach ($revenueUnits as $unit) {
            $targets[$unit->id] = array_fill(1, 12, null);
            foreach ($existing->where('unit_id', $unit->id) as $row) {
                $targets[$unit->id][$row->month] = (float) $row->target;
            }
        }

        return Inertia::render('ServicePages/MvrPlanning', [
            'year' => $year,
            'revenueUnits' => $revenueUnits,
            'targets' => $targets,
        ]);
    }

    public function storeMvrPlanning(Request $request): RedirectResponse
    {
        $page = ServicePages::find('mvr-planning');
        abort_if($page === null, 404);
        $this->authorizePage($request, $page);

        $revenueUnitIds = Unit::query()->where('unit_type', 'revenue')->pluck('id')->all();

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2050'],
            'entries' => ['required', 'array'],
            'entries.*.unit_id' => ['required', 'string', 'in:'.implode(',', $revenueUnitIds)],
            'entries.*.month' => ['required', 'integer', 'min:1', 'max:12'],
            'entries.*.target' => ['required', 'numeric', 'min:0'],
        ]);

        $year = (int) $validated['year'];
        $saved = 0;
        foreach ($validated['entries'] as $entry) {
            MvrEntry::updateOrCreate(
                [
                    'unit_id' => $entry['unit_id'],
                    'year' => $year,
                    'month' => (int) $entry['month'],
                ],
                [
                    'target' => $entry['target'],
                    'currency' => 'USD',
                ],
            );
            $saved++;
        }

        return back()->with('flash.success', __('service_pages.mvr_planning.flash_saved', ['n' => $saved]));
    }

    /**
     * @return array{slug: string, title: string, description: string, icon: string, unit_id: string|null, employee_ids: list<int>}
     */
    private function resolvePage(string $unitSlug): array
    {
        $page = ServicePages::find('daily-'.$unitSlug);
        abort_if($page === null, 404);

        return $page;
    }

    /**
     * Access is granted when the authenticated user is super-admin, OR their
     * email matches an employee whose ID is listed in the page's employee_ids.
     *
     * @param  array{employee_ids: list<int>}  $page
     */
    private function authorizePage(Request $request, array $page): void
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        if ($user->hasRole('super-admin')) {
            return;
        }

        $allowedEmails = Employee::query()
            ->whereIn('id', $page['employee_ids'])
            ->pluck('email')
            ->filter()
            ->all();

        abort_unless(in_array($user->email, $allowedEmails, true), 403);
    }
}
