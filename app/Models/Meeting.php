<?php

namespace App\Models;

use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'unit_id',
    'title',
    'type',
    'scheduled_at',
    'duration_minutes',
    'location',
    'agenda',
    'notes',
    'status',
    'created_by',
])]
class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory, LogsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['unit_id', 'title', 'type', 'scheduled_at', 'duration_minutes', 'location', 'agenda', 'notes', 'status', 'created_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('meeting');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "meeting.{$eventName}";
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
