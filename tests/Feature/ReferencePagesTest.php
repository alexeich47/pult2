<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReferencePagesTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_codex_renders_with_four_sections(): void
    {
        $this->actingAs($this->viewer())
            ->get('/codex')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Codex/Index')
                ->has('translations.codex.section.mission')
                ->has('translations.codex.section.values')
                ->has('translations.codex.section.conduct')
                ->has('translations.codex.section.standards')
                ->has('translations.codex.item.mission', 3)
                ->has('translations.codex.item.values', 3)
                ->has('translations.codex.item.conduct', 3)
                ->has('translations.codex.item.standards', 3)
            );
    }

    public function test_dictionary_exposes_all_ten_terms(): void
    {
        $this->actingAs($this->viewer())
            ->get('/dictionary')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dictionary/Index')
                ->has('translations.dictionary.terms', 10)
                ->has('translations.dictionary.terms.0.term')
                ->has('translations.dictionary.terms.0.def')
            );
    }

    public function test_info_renders_goals_features_and_changelog(): void
    {
        $this->actingAs($this->viewer())
            ->get('/info')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Info/Index')
                ->has('translations.info.goals.items', 4)
                ->has('translations.info.features.items', 8)
                ->has('translations.info.changelog.entries', 4)
                ->where('translations.info.changelog.entries.0.version', 'Phase 3')
            );
    }

    public function test_reference_pages_require_auth(): void
    {
        $this->get('/codex')->assertRedirect('/login');
        $this->get('/dictionary')->assertRedirect('/login');
        $this->get('/info')->assertRedirect('/login');
    }

    public function test_translations_change_with_locale(): void
    {
        $viewer = $this->viewer();

        $this->actingAs($viewer)->post('/locale/uk');

        $this->actingAs($viewer)
            ->get('/codex')
            ->assertInertia(fn ($page) => $page
                ->where('translations.codex.title', 'Кодекс холдингу')
            );

        $this->actingAs($viewer)->post('/locale/en');

        $this->actingAs($viewer)
            ->get('/codex')
            ->assertInertia(fn ($page) => $page
                ->where('translations.codex.title', 'Holding Codex')
            );
    }
}
