<?php

declare(strict_types=1);

namespace App\Domain\Profile\ValueObjects;

use App\Domain\Profile\Exceptions\InvalidWeight;

final readonly class Weight
{
    public function __construct(
        public float $kilograms,
    ) {
        if ($kilograms < 20.0 || $kilograms > 400.0) {
            throw new InvalidWeight;
        }
    }
}
