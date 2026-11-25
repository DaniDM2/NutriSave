<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Profile;

use App\Application\Profile\CreateProfile;
use App\Application\Profile\ShowSnapshotStatus;
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

class ShowSnapshotStatusTest extends TestCase
{
    public function test_it_reports_a_missing_profile(): void
    {
        $view = (new ShowSnapshotStatus(
            new InMemoryProfileRepository,
            new InMemoryBodySnapshotRepository,
        ))->handle(new UserId(1), new DateTimeImmutable('2026-11-24 10:00:00'));

        $this->assertFalse($view->hasProfile);
        $this->assertFalse($view->hasSnapshot);
        $this->assertFalse($view->isStale);
        $this->assertNull($view->cadenceDays);
        $this->assertNull($view->daysSinceRecorded);
    }

    public function test_it_reports_a_missing_snapshot(): void
    {
        $profiles = new InMemoryProfileRepository;
        $userId = new UserId(1);
        $this->createProfile($profiles, $userId);

        $view = (new ShowSnapshotStatus($profiles, new InMemoryBodySnapshotRepository))
            ->handle($userId, new DateTimeImmutable('2026-11-24 10:00:00'));

        $this->assertTrue($view->hasProfile);
        $this->assertFalse($view->hasSnapshot);
        $this->assertFalse($view->isStale);
        $this->assertSame(7, $view->cadenceDays);
        $this->assertNull($view->daysSinceRecorded);
    }

    public function test_it_reports_a_fresh_snapshot(): void
    {
        $profiles = new InMemoryProfileRepository;
        $snapshots = new InMemoryBodySnapshotRepository;
        $userId = new UserId(1);
        $this->createProfile($profiles, $userId);
        $this->seedSnapshot($snapshots, $userId, new DateTimeImmutable('2026-11-20 10:00:00'));

        $view = (new ShowSnapshotStatus($profiles, $snapshots))
            ->handle($userId, new DateTimeImmutable('2026-11-24 10:00:00'));

        $this->assertTrue($view->hasProfile);
        $this->assertTrue($view->hasSnapshot);
        $this->assertFalse($view->isStale);
        $this->assertSame(7, $view->cadenceDays);
        $this->assertSame(4, $view->daysSinceRecorded);
    }

    public function test_it_reports_a_stale_snapshot(): void
    {
        $profiles = new InMemoryProfileRepository;
        $snapshots = new InMemoryBodySnapshotRepository;
        $userId = new UserId(1);
        $this->createProfile($profiles, $userId);
        $this->seedSnapshot($snapshots, $userId, new DateTimeImmutable('2026-11-10 10:00:00'));

        $view = (new ShowSnapshotStatus($profiles, $snapshots))
            ->handle($userId, new DateTimeImmutable('2026-11-24 10:00:00'));

        $this->assertTrue($view->hasProfile);
        $this->assertTrue($view->hasSnapshot);
        $this->assertTrue($view->isStale);
        $this->assertSame(7, $view->cadenceDays);
        $this->assertSame(14, $view->daysSinceRecorded);
    }

    private function createProfile(InMemoryProfileRepository $profiles, UserId $userId): void
    {
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
    }

    private function seedSnapshot(
        InMemoryBodySnapshotRepository $snapshots,
        UserId $userId,
        DateTimeImmutable $recordedAt,
    ): void {
        $weight = new Weight(80.0);

        $snapshots->save(BodySnapshot::create(
            $userId,
            $weight,
            BodyMassIndex::from(new Height(180), $weight),
            SnapshotOrigin::Manual,
            $recordedAt,
        ));
    }
}
