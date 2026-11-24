<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Domain\Profile\Contracts\ProfileRepository;
use App\Domain\Profile\Exceptions\UserAlreadyHasProfile;
use App\Domain\Profile\ValueObjects\Profile;
use App\Domain\Profile\ValueObjects\ProfileId;
use App\Domain\Shared\ValueObjects\UserId;

final class InMemoryProfileRepository implements ProfileRepository
{
    /** @var array<int, Profile> */
    private array $profiles = [];

    private int $nextId = 1;

    public function save(Profile $profile): Profile
    {
        $existing = $this->findByUserId($profile->userId);

        if ($existing !== null && $existing->id?->value !== $profile->id?->value) {
            throw new UserAlreadyHasProfile;
        }

        if ($profile->id === null) {
            $profile = $profile->withId(new ProfileId($this->nextId));
            $this->nextId++;
        }

        $this->profiles[$profile->id->value] = $profile;

        return $profile;
    }

    public function findById(ProfileId $id): ?Profile
    {
        return $this->profiles[$id->value] ?? null;
    }

    public function findByUserId(UserId $userId): ?Profile
    {
        foreach ($this->profiles as $profile) {
            if ($profile->userId->value === $userId->value) {
                return $profile;
            }
        }

        return null;
    }
}
