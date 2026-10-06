<?php

namespace Tests\Feature\Livewire\Admin\Institutions;

use App\Livewire\Admin\Institutions\Manager;
use App\Models\Admin;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_institution_form_starts_with_the_default_rules(): void
    {
        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->call('openCreateForm')
            ->assertSet('form.totalPoints', Institution::DEFAULT_TOTAL_POINTS)
            ->assertSet('form.passingPercent', Institution::DEFAULT_PASSING_PERCENT)
            ->assertSet('form.maxAbsencePercent', Institution::DEFAULT_MAX_ABSENCE_PERCENT);
    }

    public function test_admin_creates_an_institution_with_its_own_rules_and_a_derived_slug(): void
    {
        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->call('openCreateForm')
            ->set('form.name', 'Universidade Federal de Exemplo')
            ->set('form.totalPoints', 100)
            ->set('form.passingPercent', 65)
            ->set('form.maxAbsencePercent', 25)
            ->call('save')
            ->assertHasNoErrors();

        $institution = Institution::sole();
        $this->assertSame('universidade-federal-de-exemplo', $institution->slug);
        $this->assertSame(65.0, $institution->passing_percent);
    }

    public function test_passing_percent_cannot_exceed_100(): void
    {
        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->set('form.name', 'Instituição')
            ->set('form.passingPercent', 101)
            ->call('save')
            ->assertHasErrors(['form.passingPercent' => 'max']);
    }

    public function test_slug_must_be_unique(): void
    {
        Institution::factory()->create(['slug' => 'ufx']);

        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->set('form.name', 'Outra')
            ->set('form.slug', 'ufx')
            ->call('save')
            ->assertHasErrors(['form.slug' => 'unique']);
    }
}
