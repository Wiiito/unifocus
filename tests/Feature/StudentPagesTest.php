<?php

namespace Tests\Feature;

use App\Enums\TermEventType;
use App\Models\AcademicTerm;
use App\Models\AcademicTermEvent;
use App\Models\Activity;
use App\Models\ClassGroup;
use App\Models\Enrollment;
use App\Models\Institution;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Páginas de leitura do estudante montadas a partir das matrículas reais.
 */
class StudentPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_ongoing_subjects_upcoming_deadlines_and_the_review_deck(): void
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->forUser($user)->create();
        Activity::factory()->for($enrollment->classGroup)->create(['title' => 'Prova de Cálculo', 'due_at' => now()->addDays(2)]);
        Question::factory()->withOptions()->for($enrollment->subject())->create(['statement' => 'Quanto é a derivada de x²?']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($enrollment->subject()->name)
            ->assertSee('Prova de Cálculo')
            ->assertSee('Quanto é a derivada de x²?');
    }

    public function test_agenda_hides_delivered_activities_and_shows_term_events(): void
    {
        $institution = Institution::factory()->create();
        $term = AcademicTerm::factory()->for($institution)->create(['starts_on' => today()->subMonth(), 'ends_on' => today()->addMonths(3)]);
        AcademicTermEvent::factory()->for($term)->create([
            'title' => 'Semana de provas',
            'type' => TermEventType::ExamWeek,
            'starts_on' => today()->addWeek(),
            'ends_on' => today()->addWeek()->addDays(4),
        ]);

        $user = User::factory()->create();
        $classGroup = ClassGroup::factory()
            ->for($user, 'createdBy')
            ->for(Subject::factory()->for($institution))
            ->for($term)
            ->create();
        $enrollment = Enrollment::factory()->for($classGroup)->for($user)->create();

        $pending = Activity::factory()->for($classGroup)->create(['title' => 'Lista pendente', 'due_at' => now()->addDays(3)]);
        $delivered = Activity::factory()->for($classGroup)->create(['title' => 'Lista entregue', 'due_at' => now()->addDays(3)]);
        $delivered->submissions()->create(['enrollment_id' => $enrollment->id, 'status' => 'submitted']);

        $this->actingAs($user)
            ->get(route('agenda'))
            ->assertOk()
            ->assertSee($pending->title)
            ->assertDontSee($delivered->title)
            ->assertSee('Semana de provas');
    }

    public function test_agenda_does_not_show_another_students_deadlines(): void
    {
        $otherEnrollment = Enrollment::factory()->forUser(User::factory()->create())->create();
        Activity::factory()->for($otherEnrollment->classGroup)->create(['title' => 'Trabalho alheio', 'due_at' => now()->addDay()]);

        $this->actingAs(User::factory()->create())
            ->get(route('agenda'))
            ->assertOk()
            ->assertDontSee('Trabalho alheio');
    }

    public function test_report_card_groups_enrollments_by_term(): void
    {
        $institution = Institution::factory()->create();
        $term = AcademicTerm::factory()->for($institution)->create(['name' => '2026.2']);
        $user = User::factory()->create();
        $classGroup = ClassGroup::factory()->for($user, 'createdBy')->for($term)->create();
        Enrollment::factory()->for($classGroup)->for($user)->create();

        $this->actingAs($user)
            ->get(route('report-card'))
            ->assertOk()
            ->assertSee('2026.2')
            ->assertSee($classGroup->subject->name);
    }
}
