<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'type',
    'name',
    'description',
    'owner_name',
    'status',
    'entry_date',
])]
class RiskEntry extends Model
{
    use LogsActivity;

    /**
     * Prefix letter used in display_id per type.
     */
    public const TYPE_PREFIXES = [
        'risk' => 'R',
        'issue' => 'I',
        'fuckup' => 'F',
        'workaround' => 'W',
    ];

    /**
     * Valid statuses per type — mirrors legacy RisksPage semantics.
     */
    public const STATUSES_BY_TYPE = [
        'risk' => ['open', 'in_progress', 'mitigated', 'closed'],
        'issue' => ['open', 'in_progress', 'closed'],
        'fuckup' => ['open', 'closed'],
        'workaround' => ['active', 'resolved'],
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'name', 'description', 'owner_name', 'status', 'entry_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('risk_entry');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "risk_entry.{$eventName}";
    }

    protected static function booted(): void
    {
        static::created(function (self $entry) {
            if (blank($entry->display_id)) {
                $entry->display_id = self::generateDisplayId($entry->type);
                $entry->saveQuietly();
            }
        });
    }

    public static function generateDisplayId(string $type): string
    {
        $count = static::query()->where('type', $type)->count();
        $prefix = self::TYPE_PREFIXES[$type] ?? 'X';

        return $prefix.'-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);
    }

    public function getRouteKeyName(): string
    {
        return 'display_id';
    }
}
