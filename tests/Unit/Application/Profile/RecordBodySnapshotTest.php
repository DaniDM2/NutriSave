<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Profile;

use App\Application\Profile\CreateProfile;
use App\Application\Profile\RecordBodySnapshot;
use App\Domain\Profile\Exceptions\InvalidCompositionPercent;
use App\Domain\Profile\Exceptions\InvalidWeight;
use App\Domain\Profile\Exceptions\ProfileNotFound;
use App\Domain\Profile\SnapshotOrigin;
use App\Domain\Shared\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\InMemoryBodySnapshotRepository;
use Tests\Fakes\InMemoryProfileRepository;

class RecordBodySnapshotTest extends TestCase
{
    public function test_it_records_a_manual_snapshot_with_bmi_from_profile_height(): void
    {
        $profiles = new InMemoryProfileRepository;
        $snapshots = new InMemoryBodySnapshotRepository;
        $userId = new UserId(1);
        $this->createProfile($profiles, $userId);

        $snapshot = (new RecordBodySnapshot($profiles, $snapshots))->handle($userId, 80.0);

        $this->assertNotNull($snapshot->id);
        $this->assertSame(1, $snapshot->userId->value);
        $this->assertSame(80.0, $snapshot->weight->kilograms);
        $this->assertSame(24.7, $snapshot->bmi->value);
        $this->assertSame(SnapshotOrigin::Manual, $snapshot->origin);
        $this->assertNull($snapshot->bodyFat);
        $this->assertNull($snapshot->muscle);
        $this->assertNull($snapshot->water);
        $this->assertSame([], $snapshot->photoPaths);
        $this->assertSame($snapshot, $snapshots->findById($snapshot->id));
    }

    public function test_it_records_optional_composition(): void
    {
        $profiles = new InMemoryProfileRepository;
        $snapshots = new InMemoryBodySnapshotRepository;
        $userId = new UserId(1);
        $this->createProfile($profiles, $userId);

        $snapshot = (new RecordBodySnapshot($profiles, $snapshots))->handle(
            $userId,
            80.0,
            18.5,
            42.0,
            55.1,
        );

        $this->assertSame(18.5, $snapshot->bodyFat?->value);
        $this->assertSame(42.0, $snapshot->muscle?->value);
        $this->assertSame(55.1, $snapshot->water?->value);
    }

    public function test_it_rejects_recording_without_a_profile(): void
    {
        $this->expectException(ProfileNotFound::class);

        (new RecordBodySnapshot(new InMemoryProfileRepository, new InMemoryBodySnapshotRepository))
            ->handle(new UserId(1), 80.0);
    }

    public function test_it_rejects_an_invalid_weight(): void
    {
        $profiles = new InMemoryProfileRepository;
        $userId = new UserId(1);
        $this->createProfile($profiles, $userId);

        $this->expectException(InvalidWeight::class);

        (new RecordBodySnapshot($profiles, new InMemoryBodySnapshotRepository))->handle($userId, 10.0);
    }

    public function test_it_rejects_an_invalid_composition_percent(): void
    {
        $profiles = new InMemoryProfileRepository;
        $userId = new UserId(1);
        $this->createProfile($profiles, $userId);

        $this->expectException(InvalidCompositionPercent::class);

        (new RecordBodySnapshot($profiles, new InMemoryBodySnapshotRepository))->handle(
            $userId,
            80.0,
            101.0,
        );
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
}
