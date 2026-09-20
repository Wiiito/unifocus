<?php

namespace Tests\Feature\Subjects;

use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSubjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_the_subjects_index(): void
    {
        $subject = Subject::factory()->create(['name' => 'Cálculo I']);

        $response = $this->get('/subjects');

        $response->assertOk();
        $response->assertSee('Cálculo I');
    }

    public function test_guests_can_view_a_subject_details_page(): void
    {
        $subject = Subject::factory()->create([
            'name' => 'Estruturas de Dados',
            'description' => 'Árvores, listas e grafos.',
        ]);

        $response = $this->get("/subjects/{$subject->id}");

        $response->assertOk();
        $response->assertSee('Estruturas de Dados');
        $response->assertSee('Árvores, listas e grafos.');
    }

    public function test_show_returns_404_for_a_nonexistent_subject(): void
    {
        $response = $this->get('/subjects/999999');

        $response->assertNotFound();
    }

    public function test_show_returns_404_for_a_soft_deleted_subject(): void
    {
        $subject = Subject::factory()->create();
        $subject->delete();

        $response = $this->get("/subjects/{$subject->id}");

        $response->assertNotFound();
    }
}
