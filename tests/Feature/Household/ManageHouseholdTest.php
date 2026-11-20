<?php

declare(strict_types=1);

namespace Tests\Feature\Household;

use App\Infrastructure\Mail\HouseholdInvitationNotification;
use App\Livewire\Household\AcceptInvitation;
use App\Livewire\Household\ManageHousehold;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ManageHouseholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_redirects_guests_to_login(): void
    {
        $this->get(route('household'))
            ->assertRedirect(route('login'));
    }

    public function test_it_shows_the_create_form_when_the_user_has_no_household(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('household'))
            ->assertOk()
            ->assertSee('Crear hogar')
            ->assertDontSee('Invitar');
    }

    public function test_it_creates_a_household(): void
    {
        $user = User::factory()->create(['name' => 'Ada Lovelace']);

        Livewire::actingAs($user)
            ->test(ManageHousehold::class)
            ->set('name', 'Casa Ada')
            ->call('create')
            ->assertHasNoErrors()
            ->assertSet('status', 'Hogar creado.')
            ->assertSet('hasHousehold', true)
            ->assertSee('Casa Ada')
            ->assertSee('Ada Lovelace')
            ->assertSee('Titular');

        $this->assertDatabaseHas('households', ['name' => 'Casa Ada']);
        $this->assertDatabaseHas('household_members', [
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_it_invites_by_email_and_lists_the_pending_invitation(): void
    {
        Notification::fake();

        $owner = User::factory()->create(['email' => 'ada@example.com']);

        Livewire::actingAs($owner)
            ->test(ManageHousehold::class)
            ->set('name', 'Casa Ada')
            ->call('create')
            ->set('inviteEmail', 'Carol@Example.com')
            ->call('invite')
            ->assertHasNoErrors()
            ->assertSet('status', 'Invitación enviada.')
            ->assertSee('carol@example.com');

        $this->assertDatabaseHas('household_invitations', [
            'email' => 'carol@example.com',
        ]);

        Notification::assertSentOnDemand(HouseholdInvitationNotification::class);
    }

    public function test_it_does_not_show_the_invite_form_to_a_member(): void
    {
        Notification::fake();

        $owner = User::factory()->create(['email' => 'ada@example.com']);
        $member = User::factory()->create(['email' => 'bob@example.com']);

        Livewire::actingAs($owner)
            ->test(ManageHousehold::class)
            ->set('name', 'Casa Ada')
            ->call('create')
            ->set('inviteEmail', $member->email)
            ->call('invite');

        $token = $this->lastInvitationToken();

        auth()->logout();

        Livewire::actingAs($member)
            ->test(AcceptInvitation::class, ['token' => $token])
            ->call('join')
            ->assertRedirect(route('household'));

        Livewire::actingAs($member)
            ->test(ManageHousehold::class)
            ->assertSet('isOwner', false)
            ->assertDontSee('Invitar');
    }

    private function lastInvitationToken(): string
    {
        $token = null;

        Notification::assertSentOnDemand(
            HouseholdInvitationNotification::class,
            function (HouseholdInvitationNotification $notification) use (&$token): bool {
                $token = $notification->plainToken;

                return true;
            },
        );

        $this->assertNotNull($token);

        return $token;
    }
}
