<?php

namespace App\Models;

use Database\Factories\RndProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'unit_id',
    'title',
    'description',
    'owner_id',
    'priority',
    'status',
    'budget',
    'currency',
    'deadline',
    'success_criteria',
    'kill_criteria',
    'started_at',
])]
class RndProject extends Model
{
    /** @use HasFactory<RndProjectFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'deadline' => 'date',
            'started_at' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['unit_id', 'title', 'description', 'owner_id', 'priority', 'status', 'budget', 'currency', 'deadline', 'success_criteria', 'kill_criteria', 'started_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('rnd_project');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "rnd_project.{$eventName}";
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'owner_id');
    }
}
