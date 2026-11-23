<?php

declare(strict_types=1);

namespace Tests\Feature\Persistence;

use App\Domain\Profile\SnapshotOrigin;
use App\Domain\Profile\ValueObjects\BodyMassIndex;
use App\Domain\Profile\ValueObjects\BodySnapshot;
use App\Domain\Profile\ValueObjects\BodySnapshotId;
use App\Domain\Profile\ValueObjects\CompositionPercent;
use App\Domain\Profile\ValueObjects\Height;
use App\Domain\Profile\ValueObjects\Weight;
use App\Domain\Shared\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\EloquentBodySnapshotRepository;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentBodySnapshotRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_a_new_snapshot_and_returns_it_with_a_persisted_id(): void
    {
        $user = User::factory()->create();
        $snapshot = $this->snapshot(new UserId((int) $user->id));

        $persisted = $this->repository()->save($snapshot);

        $this->assertNull($snapshot->id);
        $this->assertNotNull($persisted->id);
        $this->assertGreaterThan(0, $persisted->id->value);
        $this->assertSame(80.0, $persisted->weight->kilograms);
        $this->assertSame(24.7, $persisted->bmi->value);
        $this->assertSame(SnapshotOrigin::Manual, $persisted->origin);
        $this->assertDatabaseHas('body_snapshots', [
            'id' => $persisted->id->value,
            'user_id' => $user->id,
            'origin' => SnapshotOrigin::Manual->value,
        ]);
    }

    public function test_it_round_trips_composition_percents_and_photo_paths(): void
    {
        $user = User::factory()->create();
        $saved = $this->repository()->save($this->snapshot(
            new UserId((int) $user->id),
            bodyFat: new CompositionPercent(18.5),
            muscle: new CompositionPercent(42.0),
            water: new CompositionPercent(55.1),
            photoPaths: ['snapshots/1/front.jpg', 'snapshots/1/side.jpg'],
        ));

        $this->assertNotNull($saved->id);
        $found = $this->repository()->findById($saved->id);

        $this->assertNotNull($found);
        $this->assertSame($saved->id->value, $found->id?->value);
        $this->assertSame(80.0, $found->weight->kilograms);
        $this->assertSame(24.7, $found->bmi->value);
        $this->assertSame(18.5, $found->bodyFat?->value);
        $this->assertSame(42.0, $found->muscle?->value);
        $this->assertSame(55.1, $found->water?->value);
        $this->assertSame(['snapshots/1/front.jpg', 'snapshots/1/side.jpg'], $found->photoPaths);
        $this->assertEquals(
            new DateTimeImmutable('2026-11-23 10:00:00'),
            $found->recordedAt,
        );
    }

    public function test_it_returns_null_when_finding_an_unknown_snapshot_id(): void
    {
        $found = $this->repository()->findById(new BodySnapshotId(999));

        $this->assertNull($found);
    }

    public function test_it_finds_the_latest_snapshot_by_user_id(): void
    {
        $user = User::factory()->create();
        $userId = new UserId((int) $user->id);
        $repository = $this->repository();

        $repository->save($this->snapshot($userId, recordedAt: new DateTimeImmutable('2026-11-01 08:00:00')));
        $later = $repository->save($this->snapshot($userId, recordedAt: new DateTimeImmutable('2026-11-20 08:00:00')));

        $latest = $repository->findLatestByUserId($userId);

        $this->assertNotNull($latest);
        $this->assertSame($later->id?->value, $latest->id?->value);
        $this->assertEquals(new DateTimeImmutable('2026-11-20 08:00:00'), $latest->recordedAt);
    }

    public function test_it_returns_null_when_finding_latest_for_an_unknown_user(): void
    {
        $user = User::factory()->create();

        $found = $this->repository()->findLatestByUserId(new UserId((int) $user->id));

        $this->assertNull($found);
    }

    public function test_it_lists_snapshots_by_user_newest_first(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $userId = new UserId((int) $user->id);
        $repository = $this->repository();

        $older = $repository->save($this->snapshot($userId, recordedAt: new DateTimeImmutable('2026-11-01 08:00:00')));
        $newer = $repository->save($this->snapshot($userId, recordedAt: new DateTimeImmutable('2026-11-20 08:00:00')));
        $repository->save($this->snapshot(
            new UserId((int) $other->id),
            recordedAt: new DateTimeImmutable('2026-11-21 08:00:00'),
        ));

        $listed = $repository->listByUserId($userId);

        $this->assertCount(2, $listed);
        $this->assertSame($newer->id?->value, $listed[0]->id?->value);
        $this->assertSame($older->id?->value, $listed[1]->id?->value);
    }

    public function test_it_rehydrates_stored_bmi_without_recalculating(): void
    {
        $user = User::factory()->create();
        $snapshot = BodySnapshot::create(
            new UserId((int) $user->id),
            new Weight(80.0),
            BodyMassIndex::of(24.7),
            SnapshotOrigin::Manual,
            new DateTimeImmutable('2026-11-23 10:00:00'),
        );

        $saved = $this->repository()->save($snapshot);

        $this->assertNotNull($saved->id);
        $found = $this->repository()->findById($saved->id);

        $this->assertNotNull($found);
        $this->assertSame(24.7, $found->bmi->value);
    }

    /**
     * @param  list<string>  $photoPaths
     */
    private function snapshot(
        UserId $userId,
        DateTimeImmutable $recordedAt = new DateTimeImmutable('2026-11-23 10:00:00'),
        ?CompositionPercent $bodyFat = null,
        ?CompositionPercent $muscle = null,
        ?CompositionPercent $water = null,
        array $photoPaths = [],
    ): BodySnapshot {
        return BodySnapshot::create(
            $userId,
            new Weight(80.0),
            BodyMassIndex::from(new Height(180), new Weight(80.0)),
            SnapshotOrigin::Manual,
            $recordedAt,
            $bodyFat,
            $muscle,
            $water,
            $photoPaths,
        );
    }

    private function repository(): EloquentBodySnapshotRepository
    {
        return new EloquentBodySnapshotRepository;
    }
}
