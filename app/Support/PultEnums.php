<?php

namespace App\Support;

/**
 * Central registry of constrained values ported from Pult 1.2 legacy.
 * Kept as simple arrays (not PHP 8.1 enums) so they can be shared to
 * the frontend as Inertia props without serialisation ceremony.
 */
final class PultEnums
{
    /**
     * Ported from `js/data.js` DEPARTMENTS array.
     *
     * @return array<int, string>
     */
    public static function departments(): array
    {
        return [
            'Руководство',
            'Финансы',
            'Операции',
            'Продажи',
            'Технологии',
            'Продукт',
            'Маркетинг',
            'HR',
            'Юридический',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function employeeStatuses(): array
    {
        return ['active', 'vacancy'];
    }

    /**
     * @return array<int, string>
     */
    public static function supportedLocales(): array
    {
        return ['ru', 'uk', 'en'];
    }
}
