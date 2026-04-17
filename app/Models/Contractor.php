<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'unit_id',
    'name',
    'specialty',
    'contact_email',
    'contact_phone',
    'status',
    'started_at',
    'ended_at',
    'rate',
    'notes',
])]
class Contractor extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['unit_id', 'name', 'specialty', 'contact_email', 'contact_phone', 'status', 'started_at', 'ended_at', 'rate', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('contractor');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "contractor.{$eventName}";
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
