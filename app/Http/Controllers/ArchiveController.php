<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Idea;
use App\Models\RiskEntry;
use Inertia\Inertia;
use Inertia\Response;

class ArchiveController extends Controller
{
    public function __invoke(): Response
    {
        $tab = request()->query('tab', 'ideas');

        $data = match ($tab) {
            'ideas' => Idea::onlyTrashed()->with(['unit', 'author'])->orderByDesc('deleted_at')->paginate(20)->withQueryString(),
            'risks' => RiskEntry::onlyTrashed()->orderByDesc('deleted_at')->paginate(20)->withQueryString(),
            'employees' => Employee::query()->where('status', 'fired')->with(['unit'])->orderByDesc('updated_at')->paginate(20)->withQueryString(),
            default => collect(),
        };

        $counts = [
            'ideas' => Idea::onlyTrashed()->count(),
            'risks' => RiskEntry::onlyTrashed()->count(),
            'employees' => Employee::where('status', 'fired')->count(),
        ];

        return Inertia::render('Archive/Index', [
            'items' => $data,
            'tab' => $tab,
            'counts' => $counts,
        ]);
    }
}
