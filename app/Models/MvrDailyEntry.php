<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date', 'plan', 'fact', 'currency'])]
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
}
