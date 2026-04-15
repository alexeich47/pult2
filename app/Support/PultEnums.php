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

    /**
     * Ordered set — reflects the legacy IDEAS_STATUS_CONFIG order.
     *
     * @return array<int, string>
     */
    public static function ideaStatuses(): array
    {
        return ['new', 'under_review', 'approved', 'rejected', 'in_progress', 'done'];
    }

    /**
     * @return array<int, string>
     */
    public static function ideaPriorities(): array
    {
        return ['high', 'medium', 'low'];
    }

    /**
     * @return array<int, string>
     */
    public static function ideaFilterOperators(): array
    {
        return ['is', 'is_not', 'contains'];
    }

    /**
     * @return array<int, string>
     */
    public static function riskTypes(): array
    {
        return ['risk', 'issue', 'fuckup', 'workaround'];
    }

    /**
     * @return array<int, string>
     */
    public static function serviceCategories(): array
    {
        return [
            'Хостинг',
            'Инструменты',
            'Дизайн',
            'Безопасность',
            'Коммуникации',
            'Аналитика',
            'Управление',
            'Маркетинг',
            'Финансы',
            'Прочее',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function serviceCurrencies(): array
    {
        return ['USD', 'EUR', 'UAH', 'RUB'];
    }

    /**
     * @return array<int, string>
     */
    public static function serviceBillingCycles(): array
    {
        return ['monthly', 'yearly', 'once'];
    }

    /**
     * @return array<int, string>
     */
    public static function serviceStatuses(): array
    {
        return ['active', 'trial', 'inactive'];
    }
}
