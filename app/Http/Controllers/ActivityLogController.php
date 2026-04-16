<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __invoke(): Response
    {
        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest();

        // Date filter
        if ($dateFrom = request('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = request('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // User filter
        if ($userId = request('user_id')) {
            $query->where('causer_id', $userId);
        }

        // Log name filter (entity type: employee, idea, risk_entry, service)
        if ($logName = request('log_name')) {
            $query->where('log_name', $logName);
        }

        $entries = $query->paginate(30)->withQueryString();

        // Map entries for frontend
        $mapped = $entries->through(function (Activity $a): array {
            $causer = $a->causer;
            $causerName = $causer instanceof User ? $causer->name : 'System';

            return [
                'id' => $a->id,
                'description' => $a->description,
                'log_name' => $a->log_name,
                'subject_type' => $a->subject_type ? class_basename($a->subject_type) : null,
                'subject_id' => $a->subject_id,
                'causer_name' => $causerName,
                'causer_id' => $a->causer_id,
                'properties' => $a->properties?->toArray() ?? [],
                'created_at' => $a->created_at?->toISOString(),
            ];
        });

        return Inertia::render('ActivityLog/Index', [
            'entries' => $mapped,
            'filters' => [
                'date_from' => request('date_from'),
                'date_to' => request('date_to'),
                'user_id' => request('user_id'),
                'log_name' => request('log_name'),
            ],
            'users' => User::orderBy('name')->get(['id', 'name']),
            'logNames' => ['employee', 'idea', 'risk_entry', 'service'],
        ]);
    }
}
