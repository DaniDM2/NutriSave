<?php

declare(strict_types=1);

namespace App\Application\Profile;

use App\Domain\Profile\ValueObjects\BodySnapshot;
use App\Domain\Profile\ValueObjects\BodySnapshotId;
use DateTimeImmutable;

final readonly class BodySnapshotView
{
    public function __construct(
        public BodySnapshotId $id,
        public float $weightKg,
        public float $bmi,
        public ?float $bodyFat,
        public ?float $muscle,
        public ?float $water,
        public string $origin,
        public DateTimeImmutable $recordedAt,
    ) {}

    public static function fromSnapshot(BodySnapshot $snapshot): ?self
    {
        if ($snapshot->id === null) {
            return null;
        }

        return new self(
            $snapshot->id,
            $snapshot->weight->kilograms,
            $snapshot->bmi->value,
            $snapshot->bodyFat?->value,
            $snapshot->muscle?->value,
            $snapshot->water?->value,
            $snapshot->origin->value,
            $snapshot->recordedAt,
        );
    }
}
