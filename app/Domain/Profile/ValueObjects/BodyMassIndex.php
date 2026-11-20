<?php

declare(strict_types=1);

namespace App\Domain\Profile\ValueObjects;

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
}
