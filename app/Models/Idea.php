<?php

namespace App\Models;

use Database\Factories\IdeaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'unit_id',
    'author_id',
    'priority',
    'status',
    'title',
    'description',
    'impact',
])]
class Idea extends Model
{
    /** @use HasFactory<IdeaFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['unit_id', 'author_id', 'priority', 'status', 'title', 'description', 'impact'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('idea');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "idea.{$eventName}";
    }

    protected static function booted(): void
    {
        static::created(function (self $idea) {
            if (blank($idea->display_id)) {
                $idea->display_id = 'ID-'.str_pad((string) $idea->id, 3, '0', STR_PAD_LEFT);
                $idea->saveQuietly();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'display_id';
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
    public function author(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'author_id');
    }
}
