<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Profile\CreateProfile;
use App\Application\Profile\RecordBodySnapshot;
use App\Domain\Profile\Contracts\BodySnapshotRepository;
use App\Domain\Profile\SnapshotOrigin;
use App\Domain\Profile\ValueObjects\BodyMassIndex;
use App\Domain\Profile\ValueObjects\BodySnapshot;
use App\Domain\Profile\ValueObjects\Height;
use App\Domain\Profile\ValueObjects\Weight;
use App\Domain\Shared\ValueObjects\UserId;
use App\Models\User;
use DateTimeImmutable;
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
            ->assertSee('Peso')
            ->assertSee('Ir al peso')
            ->assertSee('Completa tu perfil para generar una dieta')
            ->assertSee('Generar dieta')
            ->assertSee('Ada Lovelace')
            ->assertSee('Cerrar sesión')
            ->assertDontSee('La generación de dieta llegará más adelante')
            ->assertDontSee('Tu último peso es de hace')
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
            ->assertSee('Registra tu peso para generar una dieta')
            ->assertDontSee('Completa tu perfil para generar una dieta')
            ->assertDontSee('La generación de dieta llegará más adelante')
            ->assertDontSee('Tu último peso es de hace');
    }

    public function test_it_does_not_prompt_for_a_snapshot_when_one_exists(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $userId = new UserId((int) $user->id);
        $this->app->make(CreateProfile::class)->handle(
            $userId,
            'male',
            '1996-11-21',
            180,
            'moderate',
            [],
            'maintain',
            7,
        );
        $this->app->make(RecordBodySnapshot::class)->handle($userId, 80.0);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Generar dieta')
            ->assertSee('La generación de dieta llegará más adelante')
            ->assertDontSee('Completa tu perfil para generar una dieta')
            ->assertDontSee('Registra tu peso para generar una dieta')
            ->assertDontSee('Tu último peso es de hace');
    }

    public function test_it_prompts_when_the_snapshot_is_stale(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $userId = new UserId((int) $user->id);
        $this->app->make(CreateProfile::class)->handle(
            $userId,
            'male',
            '1996-11-21',
            180,
            'moderate',
            [],
            'maintain',
            7,
        );
        $this->seedSnapshot($userId, new DateTimeImmutable('-10 days'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tu último peso es de hace')
            ->assertSee('(cadencia cada 7)')
            ->assertDontSee('Registra tu peso para generar una dieta')
            ->assertDontSee('La generación de dieta llegará más adelante');
    }

    private function seedSnapshot(UserId $userId, DateTimeImmutable $recordedAt): void
    {
        $weight = new Weight(80.0);

        $this->app->make(BodySnapshotRepository::class)->save(BodySnapshot::create(
            $userId,
            $weight,
            BodyMassIndex::from(new Height(180), $weight),
            SnapshotOrigin::Manual,
            $recordedAt,
        ));
    }
}
