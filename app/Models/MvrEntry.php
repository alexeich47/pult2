<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'unit_id',
    'year',
    'month',
    'target',
    'actual',
    'currency',
])]
class MvrEntry extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['unit_id', 'year', 'month', 'target', 'actual', 'currency'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('mvr_entry');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "mvr_entry.{$eventName}";
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
