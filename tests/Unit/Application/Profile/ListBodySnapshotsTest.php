<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Profile;

use App\Application\Profile\ListBodySnapshots;
use App\Domain\Profile\SnapshotOrigin;
use App\Domain\Profile\ValueObjects\BodyMassIndex;
use App\Domain\Profile\ValueObjects\BodySnapshot;
use App\Domain\Profile\ValueObjects\Height;
use App\Domain\Profile\ValueObjects\Weight;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\InMemoryBodySnapshotRepository;

class ListBodySnapshotsTest extends TestCase
{
    public function test_it_returns_an_empty_list_when_the_user_has_no_snapshots(): void
    {
        $views = (new ListBodySnapshots(new InMemoryBodySnapshotRepository))->handle(new UserId(1));

        $this->assertSame([], $views);
    }

    public function test_it_returns_snapshots_newest_first_for_the_user(): void
    {
        $snapshots = new InMemoryBodySnapshotRepository;
        $userId = new UserId(1);
        $older = $this->seed($snapshots, $userId, 82.0, new DateTimeImmutable('2026-11-01 10:00:00'));
        $newer = $this->seed($snapshots, $userId, 80.0, new DateTimeImmutable('2026-11-20 10:00:00'));
        $this->seed($snapshots, new UserId(2), 70.0, new DateTimeImmutable('2026-11-21 10:00:00'));

        $views = (new ListBodySnapshots($snapshots))->handle($userId);

        $this->assertCount(2, $views);
        $this->assertSame($newer->id?->value, $views[0]->id->value);
        $this->assertSame(80.0, $views[0]->weightKg);
        $this->assertSame(24.7, $views[0]->bmi);
        $this->assertSame(SnapshotOrigin::Manual->value, $views[0]->origin);
        $this->assertEquals($newer->recordedAt, $views[0]->recordedAt);
        $this->assertSame($older->id?->value, $views[1]->id->value);
        $this->assertSame(82.0, $views[1]->weightKg);
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
