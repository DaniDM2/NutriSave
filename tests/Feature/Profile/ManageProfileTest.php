<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Livewire\Profile\ManageProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_redirects_guests_to_login(): void
    {
        $this->get(route('profile'))
            ->assertRedirect(route('login'));
    }

    public function test_it_shows_the_create_form_when_the_user_has_no_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Perfil')
            ->assertSee('Guardar perfil')
            ->assertDontSee('Perfil actualizado.');
    }

    public function test_it_shows_activity_level_hints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Trabajo sentado, poco o nada de ejercicio')
            ->assertSee('Trabajo sentado y deporte 1–3 días por semana')
            ->assertSee('Trabajo sentado y deporte 4–5 días, o trabajo de pie')
            ->assertSee('Trabajo físico o entrenamiento 6–7 días por semana')
            ->assertSee('Trabajo físico y entrenamiento, o dos sesiones al día');
    }

    public function test_it_creates_a_profile(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ManageProfile::class)
            ->set('sex', 'male')
            ->set('birthDate', '1996-11-21')
            ->set('heightCm', 180)
            ->set('activityLevel', 'moderate')
            ->set('sports', ['Running'])
            ->set('goal', 'maintain')
            ->set('cadence', 7)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('status', 'Perfil guardado.')
            ->assertSet('hasProfile', true)
            ->assertSee('Running');

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'sex' => 'male',
            'height_cm' => 180,
            'activity_level' => 'moderate',
            'goal' => 'maintain',
            'cadence' => 7,
        ]);
    }

    public function test_it_hydrates_the_form_when_the_user_has_a_profile(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ManageProfile::class)
            ->set('sex', 'female')
            ->set('birthDate', '1990-05-15')
            ->set('heightCm', 165)
            ->set('activityLevel', 'active')
            ->set('sports', ['Swimming'])
            ->set('goal', 'lose')
            ->set('cadence', 14)
            ->call('save');

        Livewire::actingAs($user)
            ->test(ManageProfile::class)
            ->assertSet('hasProfile', true)
            ->assertSet('sex', 'female')
            ->assertSet('birthDate', '1990-05-15')
            ->assertSet('heightCm', 165)
            ->assertSet('activityLevel', 'active')
            ->assertSet('sports', ['Swimming'])
            ->assertSet('goal', 'lose')
            ->assertSet('cadence', 14);
    }

    public function test_it_updates_an_existing_profile(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ManageProfile::class)
            ->set('sex', 'male')
            ->set('birthDate', '1996-11-21')
            ->set('heightCm', 180)
            ->set('activityLevel', 'moderate')
            ->set('sports', ['Running'])
            ->set('goal', 'maintain')
            ->set('cadence', 7)
            ->call('save')
            ->set('heightCm', 175)
            ->set('goal', 'lose')
            ->set('cadence', 30)
            ->set('sports', ['Swimming'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('status', 'Perfil actualizado.')
            ->assertSet('hasProfile', true);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'height_cm' => 175,
            'goal' => 'lose',
            'cadence' => 30,
        ]);
        $this->assertDatabaseCount('profiles', 1);
    }

    public function test_it_validates_required_fields(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ManageProfile::class)
            ->call('save')
            ->assertHasErrors(['sex', 'birthDate', 'heightCm', 'activityLevel', 'goal', 'cadence']);
    }

    public function test_it_does_not_crash_when_creating_a_profile_that_already_exists(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ManageProfile::class)
            ->set('sex', 'male')
            ->set('birthDate', '1996-11-21')
            ->set('heightCm', 180)
            ->set('activityLevel', 'moderate')
            ->set('sports', [])
            ->set('goal', 'maintain')
            ->set('cadence', 7)
            ->call('save')
            ->set('hasProfile', false)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('status', 'Ya tienes un perfil.')
            ->assertSet('hasProfile', true);

        $this->assertDatabaseCount('profiles', 1);
    }

    public function test_it_adds_and_removes_sports(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ManageProfile::class)
            ->set('sportDraft', 'Running')
            ->call('addSport')
            ->assertSet('sports', ['Running'])
            ->assertSet('sportDraft', '')
            ->call('removeSport', 0)
            ->assertSet('sports', []);
    }
}
