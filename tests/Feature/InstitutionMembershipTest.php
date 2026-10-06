<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionMembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_links_to_an_institution_as_an_active_student(): void
    {
        $user = User::factory()->create();
        $institution = Institution::factory()->create();

        $this->actingAs($user)
            ->post(route('institution-memberships.store'), ['institution_id' => $institution->id, 'registration_code' => '2026001'])
            ->assertRedirect(route('profile.edit'));

        $membership = $user->memberships()->sole();
        $this->assertSame($institution->id, $membership->institution_id);
        $this->assertSame(MembershipRole::Student, $membership->role);
        $this->assertSame(MembershipStatus::Active, $membership->status);
        $this->assertSame('2026001', $membership->registration_code);
    }

    public function test_rejects_linking_twice_to_the_same_institution(): void
    {
        $user = User::factory()->create();
        $institution = Institution::factory()->create();
        $user->memberships()->create(['institution_id' => $institution->id]);

        $this->actingAs($user)
            ->post(route('institution-memberships.store'), ['institution_id' => $institution->id])
            ->assertSessionHasErrors(['institution_id' => __('Você já está vinculado a esta instituição.')]);
    }

    public function test_student_cannot_remove_another_students_membership(): void
    {
        $owner = User::factory()->create();
        $membership = $owner->memberships()->create(['institution_id' => Institution::factory()->create()->id]);

        $this->actingAs(User::factory()->create())
            ->delete(route('institution-memberships.destroy', $membership->id))
            ->assertNotFound();

        $this->assertModelExists($membership);
    }

    public function test_profile_page_lists_the_linked_institution(): void
    {
        $user = User::factory()->create();
        $institution = Institution::factory()->create(['name' => 'Universidade Federal Exemplo']);
        $user->memberships()->create(['institution_id' => $institution->id]);

        $this->actingAs($user)->get(route('profile.edit'))->assertOk()->assertSee('Universidade Federal Exemplo');
    }
}
