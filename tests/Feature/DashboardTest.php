<?php

declare(strict_types=1);

namespace Tests\Feature;

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

    public function test_it_renders_an_empty_dashboard_for_authenticated_users(): void
    {
        $user = User::factory()->create(['name' => 'Ada Lovelace']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Inicio')
            ->assertSee('Ada Lovelace')
            ->assertSee('Cerrar sesión')
            ->assertDontSee('Neuron AI')
            ->assertDontSee('Preguntar')
            ->assertDontSee('Descargar PDF')
            ->assertDontSee('Livewire 4');
    }
}
