<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'name',
    'url',
    'category',
    'unit_id',
    'cost',
    'currency',
    'billing',
    'next_payment',
    'status',
])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, LogsActivity;

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'next_payment' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'url', 'category', 'unit_id', 'cost', 'currency', 'billing', 'next_payment', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('service');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "service.{$eventName}";
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Monthly-equivalent cost of this service (0 for once/inactive).
     */
    public function monthlyCost(): float
    {
        if ($this->status === 'inactive') {
            return 0;
        }
        if ($this->billing === 'monthly') {
            return (float) $this->cost;
        }
        if ($this->billing === 'yearly') {
            return (float) $this->cost / 12;
        }

        return 0;
    }
}
