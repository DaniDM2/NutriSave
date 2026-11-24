<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Profile\CreateProfile;
use App\Domain\Shared\ValueObjects\UserId;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_redirects_guests_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_it_prompts_to_complete_the_profile_when_missing(): void
    {
        $user = User::factory()->create(['name' => 'Ada Lovelace']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Inicio')
            ->assertSee('Ir al hogar')
            ->assertSee('Hogar')
            ->assertSee('Perfil')
            ->assertSee('Ir al perfil')
            ->assertSee('Completa tu perfil para generar una dieta')
            ->assertSee('Generar dieta')
            ->assertSee('Ada Lovelace')
            ->assertSee('Cerrar sesión')
            ->assertDontSee('La generación de dieta llegará más adelante')
            ->assertDontSee('Neuron AI')
            ->assertDontSee('Preguntar')
            ->assertDontSee('Descargar PDF')
            ->assertDontSee('Livewire 4');
    }

    public function test_it_does_not_prompt_for_profile_when_one_exists(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->app->make(CreateProfile::class)->handle(
            new UserId((int) $user->id),
            'male',
            '1996-11-21',
            180,
            'moderate',
            [],
            'maintain',
            7,
        );

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Generar dieta')
            ->assertSee('La generación de dieta llegará más adelante')
            ->assertDontSee('Completa tu perfil para generar una dieta');
    }
}
