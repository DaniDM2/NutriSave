<?php

declare(strict_types=1);

namespace Tests\Feature\Household;

use App\Infrastructure\Mail\HouseholdInvitationNotification;
use App\Livewire\Auth\Register;
use App\Livewire\Household\AcceptInvitation;
use App\Livewire\Household\ManageHousehold;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_login_and_register_to_guests(): void
    {
        Notification::fake();
        $token = $this->inviteCarol();

        $this->get(route('household.invitations.accept', $token))
            ->assertOk()
            ->assertSee('Casa Ada')
            ->assertSee('carol@example.com')
            ->assertSee('Iniciar sesión')
            ->assertSee('Crear cuenta')
            ->assertSee(route('login', ['email' => 'carol@example.com']), false)
            ->assertSee(route('register', ['email' => 'carol@example.com']), false)
            ->assertDontSee('Unirme al hogar');
    }

    public function test_it_shows_an_error_for_an_unknown_token(): void
    {
        $this->get(route('household.invitations.accept', 'missing-token'))
            ->assertOk()
            ->assertSee('Esta invitación no es válida o ha caducado.')
            ->assertDontSee('Unirme al hogar');
    }

    public function test_it_lets_a_matching_user_join_the_household(): void
    {
        Notification::fake();
        $token = $this->inviteCarol();
        $carol = User::factory()->create([
            'name' => 'Carol',
            'email' => 'carol@example.com',
        ]);

        Livewire::actingAs($carol)
            ->test(AcceptInvitation::class, ['token' => $token])
            ->assertSee('Unirme al hogar')
            ->call('join')
            ->assertHasNoErrors()
            ->assertRedirect(route('household'));

        $this->assertDatabaseHas('household_members', [
            'user_id' => $carol->id,
            'role' => 'member',
        ]);
        $this->assertDatabaseCount('household_invitations', 0);
    }

    public function test_it_rejects_a_user_with_a_different_email(): void
    {
        Notification::fake();
        $token = $this->inviteCarol();
        $eve = User::factory()->create(['email' => 'eve@example.com']);

        Livewire::actingAs($eve)
            ->test(AcceptInvitation::class, ['token' => $token])
            ->call('join')
            ->assertSet('status', 'Debes entrar con el correo carol@example.com.');

        $this->assertDatabaseCount('household_members', 1);
    }

    public function test_it_registers_with_prefilled_email_and_returns_to_the_invitation(): void
    {
        Notification::fake();
        $token = $this->inviteCarol();
        $acceptUrl = route('household.invitations.accept', $token);

        $this->get($acceptUrl)->assertOk();

        $this->withSession(['url.intended' => $acceptUrl]);

        Livewire::withQueryParams(['email' => 'carol@example.com'])
            ->test(Register::class)
            ->assertSet('email', 'carol@example.com')
            ->set('name', 'Carol')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect($acceptUrl);

        $this->assertAuthenticated();

        Livewire::actingAs(auth()->user())
            ->test(AcceptInvitation::class, ['token' => $token])
            ->call('join')
            ->assertRedirect(route('household'));

        $this->assertDatabaseHas('household_members', [
            'user_id' => auth()->id(),
            'role' => 'member',
        ]);
    }

    private function inviteCarol(): string
    {
        $owner = User::factory()->create(['email' => 'ada@example.com']);

        Livewire::actingAs($owner)
            ->test(ManageHousehold::class)
            ->set('name', 'Casa Ada')
            ->call('create')
            ->set('inviteEmail', 'carol@example.com')
            ->call('invite');

        $token = null;

        Notification::assertSentOnDemand(
            HouseholdInvitationNotification::class,
            function (HouseholdInvitationNotification $notification) use (&$token): bool {
                $token = $notification->plainToken;

                return true;
            },
        );

        $this->assertNotNull($token);

        auth()->logout();

        return $token;
    }
}
