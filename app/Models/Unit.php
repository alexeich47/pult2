<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'name', 'color', 'unit_type', 'parent_id', 'sort_order', 'head_id', 'deputy_id', 'founded_at', 'website', 'stage', 'description', 'legal_name', 'inn'])]
class Unit extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'founded_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'head_id');
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function deputy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'deputy_id');
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Employee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
