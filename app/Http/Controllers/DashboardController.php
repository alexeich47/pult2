<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Idea;
use App\Models\MvrEntry;
use App\Models\RiskEntry;
use App\Models\Service;
use App\Support\UnitContextScope;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $personnelTotal = Employee::query()->tap(fn ($q) => UnitContextScope::apply($q))->count();
        $personnelActive = Employee::query()->where('status', 'active')->tap(fn ($q) => UnitContextScope::apply($q))->count();
        $personnelVacancies = Employee::query()->where('status', 'vacancy')->tap(fn ($q) => UnitContextScope::apply($q))->count();

        $ideasTotal = Idea::query()->tap(fn ($q) => UnitContextScope::apply($q))->count();
        $ideasByStatus = Idea::query()
            ->tap(fn ($q) => UnitContextScope::apply($q))
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();
        $ideasByPriority = Idea::query()
            ->tap(fn ($q) => UnitContextScope::apply($q))
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->all();

        // Risks are always global (no unit_id)
        $risksTotal = RiskEntry::count();
        $risksByType = RiskEntry::query()
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->all();
        $risksOpen = RiskEntry::whereIn('status', ['open', 'in_progress', 'active'])->count();

        $servicesTotal = Service::query()->tap(fn ($q) => UnitContextScope::apply($q))->count();
        $servicesActive = Service::query()->where('status', 'active')->tap(fn ($q) => UnitContextScope::apply($q))->count();
        $servicesMrr = round(
            Service::query()->tap(fn ($q) => UnitContextScope::apply($q))->get()->sum(fn (Service $s) => $s->monthlyCost()),
            2,
        );

        $mvr = MvrEntry::query()
            ->whereNull('unit_id')
            ->where('year', now()->year)
            ->orderBy('month')
            ->get(['month', 'target', 'actual']);

        return Inertia::render('Dashboard', [
            'mvr' => $mvr,
            'stats' => [
                'personnel' => [
                    'total' => $personnelTotal,
                    'active' => $personnelActive,
                    'vacancies' => $personnelVacancies,
                ],
                'ideas' => [
                    'total' => $ideasTotal,
                    'by_status' => $ideasByStatus,
                    'by_priority' => $ideasByPriority,
                ],
                'risks' => [
                    'total' => $risksTotal,
                    'by_type' => $risksByType,
                    'open' => $risksOpen,
                ],
                'services' => [
                    'total' => $servicesTotal,
                    'active' => $servicesActive,
                    'mrr' => $servicesMrr,
                ],
            ],
            'recentActivity' => Activity::query()
                ->with('subject')
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn (Activity $a) => [
                    'id' => $a->id,
                    'description' => $a->description,
                    'log_name' => $a->log_name,
                    'subject_type' => class_basename($a->subject_type ?? ''),
                    'subject_id' => $a->subject_id,
                    'properties' => $a->properties?->get('attributes', []),
                    'created_at' => $a->created_at?->toISOString(),
                ]),
        ]);
    }
}
