<?php

namespace App\Http\Controllers;

use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Http\Requests\InstitutionMembershipRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * O estudante declara onde estuda. Sem integração, não há quem aprove: o
 * vínculo já nasce ativo e libera o catálogo e os períodos da instituição.
 */
class InstitutionMembershipController extends Controller
{
    public function store(InstitutionMembershipRequest $request): RedirectResponse
    {
        $request->user()->memberships()->create([
            ...$request->validated(),
            'role' => MembershipRole::Student,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);

        return redirect()->route('profile.edit')->with('status', 'membership-created');
    }

    public function destroy(Request $request, int $membership): RedirectResponse
    {
        $request->user()->memberships()->findOrFail($membership)->delete();

        return redirect()->route('profile.edit')->with('status', 'membership-deleted');
    }
}
