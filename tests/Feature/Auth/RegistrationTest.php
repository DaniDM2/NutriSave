<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_the_register_page(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Crear cuenta');
    }

    public function test_it_prefills_email_from_the_query_string(): void
    {
        $this->get(route('register', ['email' => 'carol@example.com']))
            ->assertOk()
            ->assertSee('carol@example.com');
    }

    public function test_it_redirects_to_the_intended_url_after_registering(): void
    {
        $this->withSession(['url.intended' => '/household']);

        Livewire::test(Register::class)
            ->set('name', 'Ada Lovelace')
            ->set('email', 'ada@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect('/household');
    }

    public function test_it_registers_a_new_user(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Ada Lovelace')
            ->set('email', 'ada@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(config('fortify.home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ]);
    }

    public function test_it_does_not_register_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        Livewire::test(Register::class)
            ->set('name', 'Ada Lovelace')
            ->set('email', 'ada@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasErrors(['email']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_it_redirects_authenticated_users_away_from_register(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('register'))
            ->assertRedirect(route('dashboard'));
    }
}
