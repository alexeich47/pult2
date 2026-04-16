<?php

namespace App\Support;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UnitContextScope
{
    /**
     * Scope a query to the currently active unit (from session).
     * Includes child units of the selected unit.
     * When no unit is active (null), the query is not filtered.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function apply(Builder $query): Builder
    {
        $unitId = session('active_unit_id');

        if ($unitId) {
            $childIds = Unit::where('parent_id', $unitId)->pluck('id')->all();
            $allIds = array_merge([$unitId], $childIds);
            $query->whereIn('unit_id', $allIds);
        }

        return $query;
    }
}
