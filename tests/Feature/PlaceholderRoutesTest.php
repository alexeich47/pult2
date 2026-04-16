<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlaceholderRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, array{string, string, string}>
     */
    public static function placeholderProvider(): array
    {
        return [
            ['/strategy', 'page.strategy.title', '🎯'],
            ['/sla', 'page.sla.title', '⏱'],
            ['/processes', 'page.processes.title', '🔁'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RolesAndPermissionsSeeder::class,
            UnitSeeder::class,
        ]);
    }

    private function viewer(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('viewer', 'web'));

        return $user;
    }

    #[DataProvider('placeholderProvider')]
    public function test_placeholder_route_renders_with_correct_props(string $path, string $titleKey, string $icon): void
    {
        $expectedSlug = ltrim($path, '/');

        $this->actingAs($this->viewer())
            ->get($path)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Placeholder')
                ->where('titleKey', $titleKey)
                ->where('subtitleKey', "page.{$expectedSlug}.sub")
                ->where('icon', $icon)
                ->has("translations.page.{$expectedSlug}.title")
                ->has("translations.page.{$expectedSlug}.sub")
            );
    }

    public function test_placeholder_routes_require_auth(): void
    {
        foreach (['strategy', 'sla', 'processes'] as $slug) {
            $this->get("/{$slug}")->assertRedirect('/login');
        }
    }

    public function test_placeholder_title_translations_exist_in_all_locales(): void
    {
        $user = $this->viewer();

        foreach (['ru', 'uk', 'en'] as $locale) {
            $this->actingAs($user)->post("/locale/{$locale}");

            $this->actingAs($user)
                ->get('/strategy')
                ->assertInertia(fn ($page) => $page
                    ->has('translations.page.strategy.title')
                    ->has('translations.page.strategy.sub')
                );
        }
    }
}
