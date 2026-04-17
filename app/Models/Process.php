<?php

namespace App\Models;

use App\Support\HasVersioning;
use Database\Factories\ProcessFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'unit_id',
    'title',
    'description',
    'owner_id',
    'created_by',
    'category',
    'maturity',
    'document_url',
    'tool',
    'sort_order',
    'version',
])]
class Process extends Model
{
    /** @use HasFactory<ProcessFactory> */
    use HasFactory, HasVersioning, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['unit_id', 'title', 'description', 'owner_id', 'created_by', 'category', 'maturity', 'document_url', 'tool', 'sort_order', 'version'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('process');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "process.{$eventName}";
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
