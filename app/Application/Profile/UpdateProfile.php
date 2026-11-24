<?php

declare(strict_types=1);

namespace App\Application\Profile;

use App\Domain\Profile\ActivityLevel;
use App\Domain\Profile\Contracts\ProfileRepository;
use App\Domain\Profile\Exceptions\ProfileNotFound;
use App\Domain\Profile\Goal;
use App\Domain\Profile\Sex;
use App\Domain\Profile\SnapshotCadence;
use App\Domain\Profile\ValueObjects\BirthDate;
use App\Domain\Profile\ValueObjects\Height;
use App\Domain\Profile\ValueObjects\Profile;
use App\Domain\Profile\ValueObjects\SportName;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;

final readonly class UpdateProfile
{
    public function __construct(
        private ProfileRepository $profiles,
    ) {}

    /**
     * @param  list<string>  $sports
     */
    public function handle(
        UserId $userId,
        string $sex,
        string $birthDate,
        int $heightCm,
        string $activityLevel,
        array $sports,
        string $goal,
        int $cadence,
    ): Profile {
        $existing = $this->profiles->findByUserId($userId);

        if ($existing === null || $existing->id === null) {
            throw new ProfileNotFound;
        }

        return $this->profiles->save(new Profile(
            $userId,
            Sex::from($sex),
            new BirthDate(new DateTimeImmutable($birthDate)),
            new Height($heightCm),
            ActivityLevel::from($activityLevel),
            array_map(fn (string $sport): SportName => new SportName($sport), $sports),
            Goal::from($goal),
            SnapshotCadence::from($cadence),
            $existing->id,
        ));
    }
}
