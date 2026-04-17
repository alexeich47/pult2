<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\ServicePageAccess;
use App\Models\Unit;

class ServicePages
{
    /**
     * Registry of service pages — operational screens linked from the header.
     *
     * Page metadata (slug/title/icon/unit_id) is computed dynamically from units,
     * but per-page access (employee_ids) is stored in `service_page_access` so
     * super-admins can edit it via the UI. `defaults()` provides the initial seed
     * for first-time setup; runtime always reads from the table.
     *
     * @return list<array{slug: string, title: string, description: string, icon: string, unit_id: string|null, employee_ids: list<int>}>
     */
    public static function all(): array
    {
        $metadata = self::metadata();
        $accessByPage = ServicePageAccess::query()
            ->get(['page_slug', 'employee_id'])
            ->groupBy('page_slug');

        return array_map(
            fn (array $page) => $page + [
                'employee_ids' => $accessByPage->get($page['slug'], collect())
                    ->pluck('employee_id')
                    ->map(fn ($id) => (int) $id)
                    ->all(),
            ],
            $metadata,
        );
    }

    /**
     * @return array{slug: string, title: string, description: string, icon: string, unit_id: string|null, employee_ids: list<int>}|null
     */
    public static function find(string $slug): ?array
    {
        foreach (self::all() as $page) {
            if ($page['slug'] === $slug) {
                return $page;
            }
        }

        return null;
    }

    /**
     * Page metadata only — no DB lookup. Used both at runtime (combined with
     * access table) and by the seeder (combined with `defaults()`).
     *
     * @return list<array{slug: string, title: string, description: string, icon: string, unit_id: string|null}>
     */
    public static function metadata(): array
    {
        $pages = [];

        $revenueUnits = Unit::query()
            ->where('unit_type', 'revenue')
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        foreach ($revenueUnits as $unit) {
            $pages[] = [
                'slug' => 'daily-'.$unit->id,
                'title' => 'Daily '.$unit->name,
                'description' => 'Ежедневный ввод выручки '.$unit->name.' за вчера',
                'icon' => self::iconFor($unit->id),
                'unit_id' => $unit->id,
            ];
        }

        $pages[] = [
            'slug' => 'mvr-planning',
            'title' => 'Планирование MVR',
            'description' => 'Месячный план MVR по всем компаниям на год',
            'icon' => '🎯',
            'unit_id' => null,
        ];

        return $pages;
    }

    /**
     * Default access list per page — used by ServicePageAccessSeeder to seed
     * sensible initial values. After seeding, edits happen via the UI.
     *
     * @return list<int>
     */
    public static function defaultsFor(string $slug): array
    {
        $leadershipPositions = [
            'Managing Director',
            'Managing Partner',
            'Product Officer',
            'Chief Executive Officer (CEO)',
            'Chief Financial Officer (CFO)',
            'Chief Technical Officer (CTO)',
            'Deputy COO',
            'Growth Coordinator',
        ];

        // daily-{unit}: leadership of that unit only
        if (str_starts_with($slug, 'daily-')) {
            $unitId = substr($slug, strlen('daily-'));

            return Employee::query()
                ->where('unit_id', $unitId)
                ->where('status', 'active')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereIn('position', $leadershipPositions)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        if ($slug === 'mvr-planning') {
            // Holding leadership + every revenue-unit head.
            $revenueUnitIds = Unit::query()->where('unit_type', 'revenue')->pluck('id')->all();

            return Employee::query()
                ->where(function ($q) use ($leadershipPositions, $revenueUnitIds) {
                    $q->whereIn('position', [
                        'Chief Executive Officer (CEO)',
                        'Chief Financial Officer (CFO)',
                        'Chief Technical Officer (CTO)',
                        'Deputy COO',
                    ])->orWhere(function ($qq) use ($revenueUnitIds, $leadershipPositions) {
                        $qq->whereIn('unit_id', $revenueUnitIds)
                            ->whereIn('position', $leadershipPositions);
                    });
                })
                ->where('status', 'active')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return [];
    }

    private static function iconFor(string $unitId): string
    {
        return match ($unitId) {
            'playduck' => '🎮',
            'affcatalog' => '📊',
            'citadel' => '🏰',
            '3a' => '🎯',
            default => '💰',
        };
    }
}
