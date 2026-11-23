<?php

declare(strict_types=1);

namespace App\Domain\Profile\ValueObjects;

use App\Domain\Profile\Exceptions\InvalidPhotoPath;
use App\Domain\Profile\SnapshotOrigin;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;

final readonly class BodySnapshot
{
    /**
     * @param  list<string>  $photoPaths
     */
    public function __construct(
        public UserId $userId,
        public Weight $weight,
        public BodyMassIndex $bmi,
        public SnapshotOrigin $origin,
        public DateTimeImmutable $recordedAt,
        public ?CompositionPercent $bodyFat = null,
        public ?CompositionPercent $muscle = null,
        public ?CompositionPercent $water = null,
        public array $photoPaths = [],
        public ?BodySnapshotId $id = null,
    ) {
        $this->assertPhotoPaths();
    }

    /**
     * @param  list<string>  $photoPaths
     */
    public static function create(
        UserId $userId,
        Weight $weight,
        BodyMassIndex $bmi,
        SnapshotOrigin $origin,
        DateTimeImmutable $recordedAt,
        ?CompositionPercent $bodyFat = null,
        ?CompositionPercent $muscle = null,
        ?CompositionPercent $water = null,
        array $photoPaths = [],
    ): self {
        return new self(
            $userId,
            $weight,
            $bmi,
            $origin,
            $recordedAt,
            $bodyFat,
            $muscle,
            $water,
            $photoPaths,
        );
    }

    public function withId(BodySnapshotId $id): self
    {
        return new self(
            $this->userId,
            $this->weight,
            $this->bmi,
            $this->origin,
            $this->recordedAt,
            $this->bodyFat,
            $this->muscle,
            $this->water,
            $this->photoPaths,
            $id,
        );
    }

    private function assertPhotoPaths(): void
    {
        foreach ($this->photoPaths as $path) {
            if (trim($path) === '') {
                throw new InvalidPhotoPath;
            }
        }
    }
}
