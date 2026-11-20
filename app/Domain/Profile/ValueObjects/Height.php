<?php

declare(strict_types=1);

namespace App\Domain\Profile\ValueObjects;

use App\Domain\Profile\Exceptions\InvalidHeight;

final readonly class Height
{
    public function __construct(
        public int $centimeters,
    ) {
        if ($centimeters < 50 || $centimeters > 250) {
            throw new InvalidHeight;
        }
    }
}
