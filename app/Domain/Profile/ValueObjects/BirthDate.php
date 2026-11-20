<?php

declare(strict_types=1);

namespace App\Domain\Profile\ValueObjects;

use App\Domain\Profile\Exceptions\InvalidBirthDate;
use DateTimeImmutable;

final readonly class BirthDate
{
    public DateTimeImmutable $value;

    public function __construct(DateTimeImmutable $value)
    {
        if ((int) $value->format('Y') < 1900) {
            throw new InvalidBirthDate;
        }

        $this->value = $value->setTime(0, 0);
    }
}
