<?php

declare(strict_types=1);

namespace Tests\Feature\Persistence;

use App\Domain\Profile\ActivityLevel;
use App\Domain\Profile\Exceptions\UserAlreadyHasProfile;
use App\Domain\Profile\Goal;
use App\Domain\Profile\Sex;
use App\Domain\Profile\SnapshotCadence;
use App\Domain\Profile\ValueObjects\BirthDate;
use App\Domain\Profile\ValueObjects\Height;
use App\Domain\Profile\ValueObjects\Profile;
use App\Domain\Profile\ValueObjects\ProfileId;
use App\Domain\Profile\ValueObjects\SportName;
use App\Domain\Shared\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\EloquentProfileRepository;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentProfileRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_a_new_profile_and_returns_it_with_a_persisted_id(): void
    {
        $user = User::factory()->create();
        $profile = $this->profile(new UserId((int) $user->id), sports: [new SportName('Running')]);

        $persisted = $this->repository()->save($profile);

        $this->assertNull($profile->id);
        $this->assertNotNull($persisted->id);
        $this->assertGreaterThan(0, $persisted->id->value);
        $this->assertSame(180, $persisted->height->centimeters);
        $this->assertSame(['Running'], array_map(
            fn (SportName $sport): string => $sport->value,
            $persisted->sports,
        ));
        $this->assertDatabaseHas('profiles', [
            'id' => $persisted->id->value,
            'user_id' => $user->id,
            'sex' => Sex::Male->value,
            'height_cm' => 180,
            'activity_level' => ActivityLevel::Moderate->value,
            'goal' => Goal::Maintain->value,
            'cadence' => SnapshotCadence::Weekly->value,
        ]);
    }

    public function test_it_finds_a_profile_by_id(): void
    {
        $user = User::factory()->create();
        $saved = $this->repository()->save($this->profile(new UserId((int) $user->id)));

        $this->assertNotNull($saved->id);
        $found = $this->repository()->findById($saved->id);

        $this->assertNotNull($found);
        $this->assertSame($saved->id->value, $found->id?->value);
        $this->assertSame($user->id, $found->userId->value);
        $this->assertSame(Sex::Male, $found->sex);
        $this->assertSame('1996-11-21', $found->birthDate->value->format('Y-m-d'));
        $this->assertSame(SnapshotCadence::Weekly, $found->cadence);
    }

    public function test_it_returns_null_when_finding_an_unknown_profile_id(): void
    {
        $found = $this->repository()->findById(new ProfileId(999));

        $this->assertNull($found);
    }

    public function test_it_finds_a_profile_by_user_id(): void
    {
        $user = User::factory()->create();
        $saved = $this->repository()->save($this->profile(new UserId((int) $user->id)));

        $found = $this->repository()->findByUserId(new UserId((int) $user->id));

        $this->assertNotNull($found);
        $this->assertSame($saved->id?->value, $found->id?->value);
    }

    public function test_it_returns_null_when_finding_by_an_unknown_user_id(): void
    {
        $user = User::factory()->create();

        $found = $this->repository()->findByUserId(new UserId((int) $user->id));

        $this->assertNull($found);
    }

    public function test_it_updates_height_sports_and_cadence(): void
    {
        $user = User::factory()->create();
        $repository = $this->repository();
        $saved = $repository->save($this->profile(new UserId((int) $user->id)));

        $this->assertNotNull($saved->id);

        $updated = $repository->save(new Profile(
            $saved->userId,
            $saved->sex,
            $saved->birthDate,
            new Height(175),
            $saved->activityLevel,
            [new SportName('Swimming')],
            $saved->goal,
            SnapshotCadence::Monthly,
            $saved->id,
        ));

        $this->assertSame($saved->id->value, $updated->id?->value);
        $this->assertSame(175, $updated->height->centimeters);
        $this->assertSame(SnapshotCadence::Monthly, $updated->cadence);
        $this->assertSame(['Swimming'], array_map(
            fn (SportName $sport): string => $sport->value,
            $updated->sports,
        ));

        $found = $repository->findById($saved->id);

        $this->assertNotNull($found);
        $this->assertSame(175, $found->height->centimeters);
        $this->assertSame(SnapshotCadence::Monthly, $found->cadence);
        $this->assertSame(['Swimming'], array_map(
            fn (SportName $sport): string => $sport->value,
            $found->sports,
        ));
        $this->assertDatabaseHas('profiles', [
            'id' => $saved->id->value,
            'height_cm' => 175,
            'cadence' => SnapshotCadence::Monthly->value,
        ]);
    }

    public function test_it_rejects_saving_a_second_profile_for_the_same_user(): void
    {
        $user = User::factory()->create();
        $userId = new UserId((int) $user->id);
        $this->repository()->save($this->profile($userId));

        $this->expectException(UserAlreadyHasProfile::class);

        $this->repository()->save($this->profile($userId));
    }

    /**
     * @param  list<SportName>  $sports
     */
    private function profile(UserId $userId, array $sports = []): Profile
    {
        return Profile::create(
            $userId,
            Sex::Male,
            new BirthDate(new DateTimeImmutable('1996-11-21')),
            new Height(180),
            ActivityLevel::Moderate,
            $sports,
            Goal::Maintain,
            SnapshotCadence::Weekly,
        );
    }

    private function repository(): EloquentProfileRepository
    {
        return new EloquentProfileRepository;
    }
}
