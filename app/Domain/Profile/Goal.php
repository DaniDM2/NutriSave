<?php

declare(strict_types=1);

namespace App\Domain\Profile;

enum Goal: string
{
    case Lose = 'lose';
    case Maintain = 'maintain';
    case Gain = 'gain';

    public function calorieAdjustment(): int
    {
        return match ($this) {
            self::Lose => -500,
            self::Maintain => 0,
            self::Gain => 300,
        };
    }
}
