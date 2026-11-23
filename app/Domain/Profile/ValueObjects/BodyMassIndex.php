<?php

declare(strict_types=1);

namespace App\Domain\Profile\ValueObjects;

use App\Domain\Profile\Exceptions\InvalidBodyMassIndex;

final readonly class BodyMassIndex
{
    private function __construct(
        public float $value,
    ) {}

    public static function from(Height $height, Weight $weight): self
    {
        $meters = $height->centimeters / 100;

        return new self(round($weight->kilograms / ($meters ** 2), 1));
    }

    public static function of(float $value): self
    {
        $rounded = round($value, 1);

        if ($rounded <= 0.0) {
            throw new InvalidBodyMassIndex;
        }

        return new self($rounded);
    }
}
