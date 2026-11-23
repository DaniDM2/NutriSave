<?php

declare(strict_types=1);

namespace App\Domain\Profile\ValueObjects;

use App\Domain\Profile\Exceptions\InvalidCompositionPercent;

final readonly class CompositionPercent
{
    public float $value;

    public function __construct(float $value)
    {
        if ($value < 0.0 || $value > 100.0) {
            throw new InvalidCompositionPercent;
        }

        $this->value = round($value, 1);
    }
}
