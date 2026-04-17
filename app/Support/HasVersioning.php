<?php

namespace App\Support;

/**
 * Auto-bump a `version` string column by +0.1 on every update that changes another field.
 *
 * Expected column: `version` (string like "0.1", "0.2", ..., "1.0").
 * Create-time default is left to the schema (`default('0.1')`).
 */
trait HasVersioning
{
    public static function bootHasVersioning(): void
    {
        static::updating(function ($model) {
            // Ignore updates that only touch the version column itself or timestamps.
            $dirty = $model->getDirty();
            unset($dirty['version'], $dirty['updated_at']);
            if (empty($dirty)) {
                return;
            }

            $current = (float) ($model->getOriginal('version') ?? '0.0');
            $next = round($current + 0.1, 1);
            $model->version = number_format($next, 1, '.', '');
        });
    }
}
