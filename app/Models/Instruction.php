<?php

namespace App\Models;

use Database\Factories\InstructionFactory;
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
    'content',
    'checklist_items',
    'created_by',
])]
class Instruction extends Model
{
    /** @use HasFactory<InstructionFactory> */
    use HasFactory, LogsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'checklist_items' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['unit_id', 'title', 'type', 'content', 'checklist_items', 'created_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('instruction');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "instruction.{$eventName}";
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
