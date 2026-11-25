<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Profile;

use App\Application\Profile\CreateProfile;
use App\Application\Profile\RecordBodySnapshot;
use App\Application\Profile\ShowLatestBodySnapshot;
use App\Domain\Profile\SnapshotOrigin;
use App\Domain\Profile\ValueObjects\BodyMassIndex;
use App\Domain\Profile\ValueObjects\BodySnapshot;
use App\Domain\Profile\ValueObjects\Height;
use App\Domain\Profile\ValueObjects\Weight;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\InMemoryBodySnapshotRepository;
use Tests\Fakes\InMemoryProfileRepository;

class ShowLatestBodySnapshotTest extends TestCase
{
    public function test_it_returns_null_when_the_user_has_no_snapshots(): void
    {
        $view = (new ShowLatestBodySnapshot(new InMemoryBodySnapshotRepository))->handle(new UserId(1));

        $this->assertNull($view);
    }

    public function test_it_returns_a_view_for_the_latest_snapshot(): void
    {
        $profiles = new InMemoryProfileRepository;
        $snapshots = new InMemoryBodySnapshotRepository;
        $userId = new UserId(1);
        (new CreateProfile($profiles))->handle(
            $userId,
            'male',
            '1996-11-21',
            180,
            'moderate',
            [],
            'maintain',
            7,
        );
        $recorded = (new RecordBodySnapshot($profiles, $snapshots))->handle(
            $userId,
            80.0,
            18.5,
            42.0,
            55.1,
        );

        $view = (new ShowLatestBodySnapshot($snapshots))->handle($userId);

        $this->assertNotNull($view);
        $this->assertSame($recorded->id?->value, $view->id->value);
        $this->assertSame(80.0, $view->weightKg);
        $this->assertSame(24.7, $view->bmi);
        $this->assertSame(18.5, $view->bodyFat);
        $this->assertSame(42.0, $view->muscle);
        $this->assertSame(55.1, $view->water);
        $this->assertSame(SnapshotOrigin::Manual->value, $view->origin);
        $this->assertEquals($recorded->recordedAt, $view->recordedAt);
    }

    public function test_it_returns_the_newest_snapshot_for_the_user(): void
    {
        $snapshots = new InMemoryBodySnapshotRepository;
        $userId = new UserId(1);
        $older = $this->seed($snapshots, $userId, 82.0, new DateTimeImmutable('2026-11-01 10:00:00'));
        $newer = $this->seed($snapshots, $userId, 80.0, new DateTimeImmutable('2026-11-20 10:00:00'));
        $this->seed($snapshots, new UserId(2), 70.0, new DateTimeImmutable('2026-11-21 10:00:00'));

        $view = (new ShowLatestBodySnapshot($snapshots))->handle($userId);

        $this->assertNotNull($view);
        $this->assertSame($newer->id?->value, $view->id->value);
        $this->assertSame(80.0, $view->weightKg);
        $this->assertNotSame($older->id?->value, $view->id->value);
    }

    private function seed(
        InMemoryBodySnapshotRepository $snapshots,
        UserId $userId,
        float $weightKg,
        DateTimeImmutable $recordedAt,
    ): BodySnapshot {
        $weight = new Weight($weightKg);

        return $snapshots->save(BodySnapshot::create(
            $userId,
            $weight,
            BodyMassIndex::from(new Height(180), $weight),
            SnapshotOrigin::Manual,
            $recordedAt,
        ));
    }
}
