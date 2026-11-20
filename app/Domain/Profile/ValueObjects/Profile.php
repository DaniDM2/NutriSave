<?php

declare(strict_types=1);

namespace App\Domain\Profile\ValueObjects;

use App\Domain\Profile\ActivityLevel;
use App\Domain\Profile\Exceptions\DuplicateSport;
use App\Domain\Profile\Exceptions\InvalidBirthDate;
use App\Domain\Profile\Goal;
use App\Domain\Profile\Sex;
use App\Domain\Profile\SnapshotCadence;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;

final readonly class Profile
{
    /**
     * @param  list<SportName>  $sports
     */
    public function __construct(
        public UserId $userId,
        public Sex $sex,
        public BirthDate $birthDate,
        public Height $height,
        public ActivityLevel $activityLevel,
        public array $sports,
        public Goal $goal,
        public SnapshotCadence $cadence,
        public ?ProfileId $id = null,
    ) {
        $this->assertUniqueSports();
    }

    /**
     * @param  list<SportName>  $sports
     */
    public static function create(
        UserId $userId,
        Sex $sex,
        BirthDate $birthDate,
        Height $height,
        ActivityLevel $activityLevel,
        array $sports,
        Goal $goal,
        SnapshotCadence $cadence,
    ): self {
        return new self(
            $userId,
            $sex,
            $birthDate,
            $height,
            $activityLevel,
            $sports,
            $goal,
            $cadence,
        );
    }

    public function withId(ProfileId $id): self
    {
        return new self(
            $this->userId,
            $this->sex,
            $this->birthDate,
            $this->height,
            $this->activityLevel,
            $this->sports,
            $this->goal,
            $this->cadence,
            $id,
        );
    }

    public function ageOn(DateTimeImmutable $on): int
    {
        $onDate = $on->setTime(0, 0);

        if ($onDate <= $this->birthDate->value) {
            throw new InvalidBirthDate;
        }

        return $this->birthDate->value->diff($onDate)->y;
    }

    private function assertUniqueSports(): void
    {
        $names = array_map(
            fn (SportName $sport): string => mb_strtolower($sport->value),
            $this->sports,
        );

        if (count($names) !== count(array_unique($names))) {
            throw new DuplicateSport;
        }
    }
}
