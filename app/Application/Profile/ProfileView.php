<?php

declare(strict_types=1);

namespace App\Application\Profile;

use App\Domain\Profile\ValueObjects\ProfileId;

final readonly class ProfileView
{
    /**
     * @param  list<string>  $sports
     */
    public function __construct(
        public ProfileId $id,
        public string $sex,
        public string $birthDate,
        public int $heightCm,
        public string $activityLevel,
        public array $sports,
        public string $goal,
        public int $cadence,
    ) {}
}
