<?php

declare(strict_types=1);

namespace App\Domain\Profile\ValueObjects;

use App\Domain\Profile\Sex;
use DateTimeImmutable;

final readonly class EnergyTargets
{
    private function __construct(
        public int $bmrKcal,
        public int $tdeeKcal,
        public int $targetKcal,
        public int $proteinGrams,
        public int $fatGrams,
        public int $carbohydrateGrams,
    ) {}

    public static function for(Profile $profile, Weight $weight, DateTimeImmutable $on): self
    {
        $bmr = self::basalMetabolicRate($profile, $weight, $profile->ageOn($on));
        $tdee = (int) round($bmr * $profile->activityLevel->multiplier());
        $target = max($bmr, $tdee + $profile->goal->calorieAdjustment());
        $proteinGrams = (int) round(2.0 * $weight->kilograms);
        $fatGrams = (int) round(1.0 * $weight->kilograms);
        $remainingKcal = $target - $proteinGrams * 4 - $fatGrams * 9;
        $carbohydrateGrams = max(0, (int) round($remainingKcal / 4));

        return new self($bmr, $tdee, $target, $proteinGrams, $fatGrams, $carbohydrateGrams);
    }

    private static function basalMetabolicRate(Profile $profile, Weight $weight, int $age): int
    {
        $constant = $profile->sex === Sex::Male ? 5 : -161;

        return (int) round(
            10 * $weight->kilograms + 6.25 * $profile->height->centimeters - 5 * $age + $constant,
        );
    }
}
