<?php

declare(strict_types=1);

namespace App\Domain\Household\Contracts;

use App\Domain\Household\ValueObjects\Household;
use App\Domain\Household\ValueObjects\HouseholdId;
use App\Domain\Shared\ValueObjects\UserId;

interface HouseholdRepository
{
    public function save(Household $household): Household;

    public function findById(HouseholdId $id): ?Household;

    public function findByUserId(UserId $userId): ?Household;
}
