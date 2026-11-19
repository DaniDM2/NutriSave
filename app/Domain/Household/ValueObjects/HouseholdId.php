<?php

declare(strict_types=1);

namespace App\Domain\Household\ValueObjects;

use App\Domain\Household\Exceptions\InvalidHouseholdId;

final readonly class HouseholdId
{
    public function __construct(
        public int $value,
    ) {
        if ($value < 1) {
            throw new InvalidHouseholdId;
        }
    }
}
