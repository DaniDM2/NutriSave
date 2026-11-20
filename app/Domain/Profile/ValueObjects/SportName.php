<?php

declare(strict_types=1);

namespace App\Domain\Profile\ValueObjects;

use App\Domain\Profile\Exceptions\InvalidSportName;

final readonly class SportName
{
    public string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '' || mb_strlen($trimmed) > 50) {
            throw new InvalidSportName;
        }

        $this->value = $trimmed;
    }
}
