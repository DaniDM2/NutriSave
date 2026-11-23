<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Profile;

use App\Domain\Profile\Exceptions\InvalidPhotoPath;
use App\Domain\Profile\SnapshotOrigin;
use App\Domain\Profile\ValueObjects\BodyMassIndex;
use App\Domain\Profile\ValueObjects\BodySnapshot;
use App\Domain\Profile\ValueObjects\BodySnapshotId;
use App\Domain\Profile\ValueObjects\CompositionPercent;
use App\Domain\Profile\ValueObjects\Height;
use App\Domain\Profile\ValueObjects\Weight;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class BodySnapshotTest extends TestCase
{
    public function test_it_creates_a_snapshot_without_a_persisted_id(): void
    {
        $snapshot = $this->snapshot();

        $this->assertNull($snapshot->id);
        $this->assertSame(1, $snapshot->userId->value);
        $this->assertSame(80.0, $snapshot->weight->kilograms);
        $this->assertSame(24.7, $snapshot->bmi->value);
        $this->assertSame(SnapshotOrigin::Manual, $snapshot->origin);
        $this->assertNull($snapshot->bodyFat);
        $this->assertNull($snapshot->muscle);
        $this->assertNull($snapshot->water);
        $this->assertSame([], $snapshot->photoPaths);
        $this->assertEquals(new DateTimeImmutable('2026-11-23 10:00:00'), $snapshot->recordedAt);
    }

    public function test_it_assigns_a_persisted_id(): void
    {
        $snapshot = $this->snapshot();

        $persisted = $snapshot->withId(new BodySnapshotId(7));

        $this->assertNull($snapshot->id);
        $this->assertSame(7, $persisted->id?->value);
        $this->assertSame(1, $persisted->userId->value);
    }

    public function test_it_accepts_optional_composition_and_photo_paths(): void
    {
        $snapshot = $this->snapshot(
            bodyFat: new CompositionPercent(18.5),
            muscle: new CompositionPercent(42.0),
            water: new CompositionPercent(55.1),
            photoPaths: ['snapshots/1/front.jpg'],
        );

        $this->assertSame(18.5, $snapshot->bodyFat?->value);
        $this->assertSame(42.0, $snapshot->muscle?->value);
        $this->assertSame(55.1, $snapshot->water?->value);
        $this->assertSame(['snapshots/1/front.jpg'], $snapshot->photoPaths);
    }

    public function test_it_rejects_an_empty_photo_path(): void
    {
        $this->expectException(InvalidPhotoPath::class);

        $this->snapshot(photoPaths: ['']);
    }

    public function test_it_rejects_a_whitespace_only_photo_path(): void
    {
        $this->expectException(InvalidPhotoPath::class);

        $this->snapshot(photoPaths: ['   ']);
    }

    /**
     * @param  list<string>  $photoPaths
     */
    private function snapshot(
        ?CompositionPercent $bodyFat = null,
        ?CompositionPercent $muscle = null,
        ?CompositionPercent $water = null,
        array $photoPaths = [],
    ): BodySnapshot {
        return BodySnapshot::create(
            new UserId(1),
            new Weight(80.0),
            BodyMassIndex::from(new Height(180), new Weight(80.0)),
            SnapshotOrigin::Manual,
            new DateTimeImmutable('2026-11-23 10:00:00'),
            $bodyFat,
            $muscle,
            $water,
            $photoPaths,
        );
    }
}
