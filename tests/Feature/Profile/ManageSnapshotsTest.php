<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Application\Profile\CreateProfile;
use App\Domain\Profile\Contracts\BodySnapshotRepository;
use App\Domain\Profile\SnapshotOrigin;
use App\Domain\Profile\ValueObjects\BodyMassIndex;
use App\Domain\Profile\ValueObjects\BodySnapshot;
use App\Domain\Profile\ValueObjects\Height;
use App\Domain\Profile\ValueObjects\Weight;
use App\Domain\Shared\ValueObjects\UserId;
use App\Livewire\Profile\ManageSnapshots;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageSnapshotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_redirects_guests_to_login(): void
    {
        $this->get(route('snapshots'))
            ->assertRedirect(route('login'));
    }

    public function test_it_prompts_to_complete_the_profile_when_missing(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('snapshots'))
            ->assertOk()
            ->assertSee('Peso')
            ->assertSee('Completa tu perfil para registrar el peso')
            ->assertSee('Ir al perfil')
            ->assertDontSee('Registrar peso')
            ->assertDontSee('Aún no hay registros.')
            ->assertDontSee('Tu último peso es de hace');
    }

    public function test_it_shows_the_form_and_empty_history_when_the_user_has_a_profile(): void
    {
        $user = $this->userWithProfile();

        $this->actingAs($user)
            ->get(route('snapshots'))
            ->assertOk()
            ->assertSee('Peso')
            ->assertSee('Registrar peso')
            ->assertSee('Aún no hay registros.')
            ->assertDontSee('Completa tu perfil para registrar el peso')
            ->assertDontSee('Tu último peso es de hace');
    }

    public function test_it_records_a_manual_snapshot(): void
    {
        $user = $this->userWithProfile();

        Livewire::actingAs($user)
            ->test(ManageSnapshots::class)
            ->set('weightKg', 80)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('status', 'Peso registrado.')
            ->assertDontSee('Aún no hay registros.')
            ->assertSee('80')
            ->assertSee('24.7');

        $this->assertDatabaseHas('body_snapshots', [
            'user_id' => $user->id,
            'weight_kg' => 80,
            'bmi' => 24.7,
            'origin' => 'manual',
            'body_fat_percent' => null,
            'muscle_percent' => null,
            'water_percent' => null,
        ]);
        $this->assertDatabaseCount('body_snapshots', 1);
    }

    public function test_it_records_optional_composition(): void
    {
        $user = $this->userWithProfile();

        Livewire::actingAs($user)
            ->test(ManageSnapshots::class)
            ->set('weightKg', 80)
            ->set('bodyFat', 18.5)
            ->set('muscle', 42)
            ->set('water', 55.1)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('18.5')
            ->assertSee('42')
            ->assertSee('55.1');

        $this->assertDatabaseHas('body_snapshots', [
            'user_id' => $user->id,
            'body_fat_percent' => 18.5,
            'muscle_percent' => 42.0,
            'water_percent' => 55.1,
        ]);
    }

    public function test_it_lists_snapshots_newest_first_on_remount(): void
    {
        $user = $this->userWithProfile();

        Livewire::actingAs($user)
            ->test(ManageSnapshots::class)
            ->set('weightKg', 82)
            ->call('save')
            ->set('weightKg', 80)
            ->call('save');

        Livewire::actingAs($user)
            ->test(ManageSnapshots::class)
            ->assertSeeInOrder(['80', '82']);
    }

    public function test_it_validates_required_weight(): void
    {
        $user = $this->userWithProfile();

        Livewire::actingAs($user)
            ->test(ManageSnapshots::class)
            ->call('save')
            ->assertHasErrors(['weightKg']);

        $this->assertDatabaseCount('body_snapshots', 0);
    }

    public function test_it_does_not_record_when_the_profile_is_missing(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ManageSnapshots::class)
            ->set('weightKg', 80)
            ->call('save')
            ->assertSet('status', 'Completa tu perfil para registrar el peso.')
            ->assertSet('hasProfile', false);

        $this->assertDatabaseCount('body_snapshots', 0);
    }

    public function test_it_shows_a_stale_notice_until_a_new_snapshot_is_recorded(): void
    {
        $user = $this->userWithProfile();
        $this->seedSnapshot(new UserId((int) $user->id), new DateTimeImmutable('-10 days'));

        Livewire::actingAs($user)
            ->test(ManageSnapshots::class)
            ->assertSee('Tu último peso es de hace')
            ->assertSee('(cadencia cada 7)')
            ->set('weightKg', 79)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDontSee('Tu último peso es de hace');
    }

    private function userWithProfile(): User
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

        return $user;
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
