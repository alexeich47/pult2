<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['unit_id', 'date', 'plan', 'fact', 'currency'])]
class MvrDailyEntry extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'plan' => 'decimal:2',
            'fact' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
