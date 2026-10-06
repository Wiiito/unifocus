<?php

namespace Tests\Feature\Livewire\Admin\AcademicTerms;

use App\Enums\TermEventType;
use App\Livewire\Admin\AcademicTerms\Manager;
use App\Models\AcademicTerm;
use App\Models\Admin;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_a_term_with_its_calendar_events(): void
    {
        $institution = Institution::factory()->create();

        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->call('openCreateForm')
            ->set('form.institutionId', $institution->id)
            ->set('form.name', '2026.2')
            ->set('form.startsOn', '2026-08-01')
            ->set('form.endsOn', '2026-12-15')
            ->call('addEvent')
            ->set('form.events.0.title', 'Semana de provas P1')
            ->set('form.events.0.type', TermEventType::ExamWeek->value)
            ->set('form.events.0.starts_on', '2026-09-21')
            ->set('form.events.0.ends_on', '2026-09-25')
            ->call('save')
            ->assertHasNoErrors();

        $term = AcademicTerm::sole();
        $this->assertSame($institution->id, $term->institution_id);
        $this->assertSame('Semana de provas P1', $term->events()->sole()->title);
    }

    public function test_term_requires_an_institution(): void
    {
        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->set('form.name', '2026.2')
            ->set('form.startsOn', '2026-08-01')
            ->set('form.endsOn', '2026-12-15')
            ->call('save')
            ->assertHasErrors(['form.institutionId' => 'required']);
    }

    public function test_event_must_fall_inside_the_term(): void
    {
        $institution = Institution::factory()->create();

        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->set('form.institutionId', $institution->id)
            ->set('form.name', '2026.2')
            ->set('form.startsOn', '2026-08-01')
            ->set('form.endsOn', '2026-12-15')
            ->call('addEvent')
            ->set('form.events.0.title', 'Recesso')
            ->set('form.events.0.starts_on', '2027-01-10')
            ->set('form.events.0.ends_on', '2027-01-20')
            ->call('save')
            ->assertHasErrors(['form.events.0.starts_on']);

        $this->assertSame(0, AcademicTerm::count());
    }

    public function test_the_same_term_name_can_exist_in_different_institutions(): void
    {
        AcademicTerm::factory()->create(['name' => '2026.2']);
        $institution = Institution::factory()->create();

        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->set('form.institutionId', $institution->id)
            ->set('form.name', '2026.2')
            ->set('form.startsOn', '2026-08-01')
            ->set('form.endsOn', '2026-12-15')
            ->call('save')
            ->assertHasNoErrors();
    }
}
