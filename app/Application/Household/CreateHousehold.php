<?php

declare(strict_types=1);

namespace App\Application\Household;

use App\Domain\Household\Contracts\HouseholdRepository;
use App\Domain\Household\Exceptions\UserAlreadyInHousehold;
use App\Domain\Household\ValueObjects\Household;
use App\Domain\Household\ValueObjects\HouseholdName;
use App\Domain\Shared\ValueObjects\UserId;

final readonly class CreateHousehold
{
    public function __construct(
        private HouseholdRepository $households,
    ) {}

    public function handle(UserId $ownerId, string $name): Household
    {
        if ($this->households->findByUserId($ownerId) !== null) {
            throw new UserAlreadyInHousehold;
        }

        return $this->households->save(Household::create(new HouseholdName($name), $ownerId));
    }
}
