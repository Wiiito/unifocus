<?php

namespace Tests\Feature\Livewire\Admin\Subjects;

use App\Livewire\Admin\Subjects\Manager;
use App\Models\Admin;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_successfully_for_an_authenticated_admin(): void
    {
        $admin = Admin::factory()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(Manager::class)
            ->assertStatus(200);
    }

    public function test_admin_can_create_a_subject(): void
    {
        $admin = Admin::factory()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(Manager::class)
            ->set('name', 'Álgebra Linear')
            ->set('code', 'MAT201')
            ->set('credits', 4)
            ->set('workloadHours', 60)
            ->call('save')
            ->assertHasNoErrors();

        $subject = Subject::where('code', 'MAT201')->firstOrFail();

        $this->assertSame('Álgebra Linear', $subject->name);
        $this->assertSame(4, $subject->credits);
        $this->assertSame(60, $subject->workload_hours);
        $this->assertSame($admin->id, $subject->created_by_admin_id);
    }

    public function test_name_is_required_to_create_a_subject(): void
    {
        $admin = Admin::factory()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(Manager::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);

        $this->assertSame(0, Subject::count());
    }

    public function test_code_must_be_unique(): void
    {
        $admin = Admin::factory()->create();
        Subject::factory()->create(['code' => 'MAT101']);

        Livewire::actingAs($admin, 'admin')
            ->test(Manager::class)
            ->set('name', 'Outra Matéria')
            ->set('code', 'MAT101')
            ->call('save')
            ->assertHasErrors(['code' => 'unique']);
    }

    public function test_admin_can_update_a_subject(): void
    {
        $admin = Admin::factory()->create();
        $subject = Subject::factory()->create(['name' => 'Nome Antigo']);

        Livewire::actingAs($admin, 'admin')
            ->test(Manager::class)
            ->call('openEditForm', $subject->id)
            ->set('name', 'Nome Atualizado')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nome Atualizado', $subject->fresh()->name);
    }

    public function test_updating_a_subject_can_keep_its_own_code(): void
    {
        $admin = Admin::factory()->create();
        $subject = Subject::factory()->create(['code' => 'MAT101']);

        Livewire::actingAs($admin, 'admin')
            ->test(Manager::class)
            ->call('openEditForm', $subject->id)
            ->set('name', 'Nome Atualizado')
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_admin_can_delete_a_subject(): void
    {
        $admin = Admin::factory()->create();
        $subject = Subject::factory()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(Manager::class)
            ->call('deleteSubject', $subject->id);

        $this->assertSoftDeleted($subject);
    }
}
