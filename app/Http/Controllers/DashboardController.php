<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Idea;
use App\Models\RiskEntry;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'personnel' => [
                    'total' => Employee::count(),
                    'active' => Employee::where('status', 'active')->count(),
                    'vacancies' => Employee::where('status', 'vacancy')->count(),
                ],
                'ideas' => [
                    'total' => Idea::count(),
                    'by_status' => Idea::query()
                        ->selectRaw('status, count(*) as count')
                        ->groupBy('status')
                        ->pluck('count', 'status')
                        ->all(),
                    'by_priority' => Idea::query()
                        ->selectRaw('priority, count(*) as count')
                        ->groupBy('priority')
                        ->pluck('count', 'priority')
                        ->all(),
                ],
                'risks' => [
                    'total' => RiskEntry::count(),
                    'by_type' => RiskEntry::query()
                        ->selectRaw('type, count(*) as count')
                        ->groupBy('type')
                        ->pluck('count', 'type')
                        ->all(),
                    'open' => RiskEntry::whereIn('status', ['open', 'in_progress', 'active'])->count(),
                ],
                'services' => [
                    'total' => Service::count(),
                    'active' => Service::where('status', 'active')->count(),
                    'mrr' => round(Service::all()->sum(fn (Service $s) => $s->monthlyCost()), 2),
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
