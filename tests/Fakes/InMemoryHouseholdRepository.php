<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Domain\Household\Contracts\HouseholdRepository;
use App\Domain\Household\Exceptions\UserAlreadyInHousehold;
use App\Domain\Household\ValueObjects\Household;
use App\Domain\Household\ValueObjects\HouseholdId;
use App\Domain\Shared\ValueObjects\UserId;

final class InMemoryHouseholdRepository implements HouseholdRepository
{
    /** @var array<int, Household> */
    private array $households = [];

    private int $nextId = 1;

    public function save(Household $household): Household
    {
        foreach ($household->members as $member) {
            $existing = $this->findByUserId($member->userId);

            if ($existing !== null && $existing->id?->value !== $household->id?->value) {
                throw new UserAlreadyInHousehold;
            }
        }

        if ($household->id === null) {
            $household = $household->withId(new HouseholdId($this->nextId));
            $this->nextId++;
        }

        $this->households[$household->id->value] = $household;

        return $household;
    }

    public function findById(HouseholdId $id): ?Household
    {
        return $this->households[$id->value] ?? null;
    }

    public function findByUserId(UserId $userId): ?Household
    {
        foreach ($this->households as $household) {
            if ($household->hasMember($userId)) {
                return $household;
            }
        }

        return null;
    }
}
