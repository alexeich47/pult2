<?php

namespace App\Models;

use Database\Factories\IdeaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
    'score_time',
    'score_headcount',
    'score_reach',
    'score_impact',
    'score_confidence',
    'score_effort',
])]
class Idea extends Model
{
    /** @use HasFactory<IdeaFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    /** Sum of the 6 THRICE scores, null if any component is missing. */
    protected function thriceScore(): Attribute
    {
        return Attribute::make(
            get: function (): ?int {
                $parts = [
                    $this->score_time, $this->score_headcount, $this->score_reach,
                    $this->score_impact, $this->score_confidence, $this->score_effort,
                ];
                if (in_array(null, $parts, true)) {
                    return null;
                }

                return (int) array_sum($parts);
            },
        );
    }

    /** @var list<string> */
    protected $appends = ['thrice_score'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'unit_id', 'author_id', 'priority', 'status', 'title', 'description', 'impact',
                'score_time', 'score_headcount', 'score_reach',
                'score_impact', 'score_confidence', 'score_effort',
            ])
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
