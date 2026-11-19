<?php

declare(strict_types=1);

namespace App\Domain\Household\ValueObjects;

use App\Domain\Household\Exceptions\InvalidHouseholdName;

final readonly class HouseholdName
{
    public string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '' || mb_strlen($trimmed) > 255) {
            throw new InvalidHouseholdName;
        }

        $this->value = $trimmed;
    }
}
