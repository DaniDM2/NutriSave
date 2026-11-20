<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_the_login_page(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Iniciar sesión');
    }

    public function test_it_prefills_email_from_the_query_string(): void
    {
        $this->get(route('login', ['email' => 'carol@example.com']))
            ->assertOk()
            ->assertSee('carol@example.com');
    }

    public function test_it_authenticates_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(config('fortify.home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_it_does_not_authenticate_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_it_redirects_authenticated_users_away_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }
}
