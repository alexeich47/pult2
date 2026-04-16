<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Idea;
use App\Models\Service;
use App\Models\Unit;
use Inertia\Inertia;
use Inertia\Response;

class UnitDashboardController extends Controller
{
    public function __invoke(Unit $unit): Response
    {
        return Inertia::render('Units/Show', [
            'unit' => $unit,
            'stats' => [
                'personnel' => [
                    'total' => Employee::where('unit_id', $unit->id)->count(),
                    'active' => Employee::where('unit_id', $unit->id)->where('status', 'active')->count(),
                    'vacancies' => Employee::where('unit_id', $unit->id)->where('status', 'vacancy')->count(),
                    'fired' => Employee::where('unit_id', $unit->id)->where('status', 'fired')->count(),
                ],
                'ideas' => [
                    'total' => Idea::where('unit_id', $unit->id)->count(),
                    'by_status' => Idea::query()
                        ->where('unit_id', $unit->id)
                        ->selectRaw('status, count(*) as count')
                        ->groupBy('status')
                        ->pluck('count', 'status')
                        ->all(),
                ],
                'services' => [
                    'total' => Service::where('unit_id', $unit->id)->count(),
                    'active' => Service::where('unit_id', $unit->id)->where('status', 'active')->count(),
                    'mrr' => round(
                        Service::where('unit_id', $unit->id)->get()->sum(fn (Service $s) => $s->monthlyCost()),
                        2,
                    ),
                ],
            ],
            'employees' => Employee::query()
                ->where('unit_id', $unit->id)
                ->where('status', 'active')
                ->with('manager')
                ->orderBy('name')
                ->limit(10)
                ->get(),
            'ideas' => Idea::query()
                ->where('unit_id', $unit->id)
                ->with('author')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
            'services' => Service::query()
                ->where('unit_id', $unit->id)
                ->orderBy('name')
                ->limit(10)
                ->get(),
        ]);
    }
}
