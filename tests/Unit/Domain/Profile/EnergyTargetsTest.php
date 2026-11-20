<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Profile;

use App\Domain\Profile\ActivityLevel;
use App\Domain\Profile\Goal;
use App\Domain\Profile\Sex;
use App\Domain\Profile\SnapshotCadence;
use App\Domain\Profile\ValueObjects\BirthDate;
use App\Domain\Profile\ValueObjects\EnergyTargets;
use App\Domain\Profile\ValueObjects\Height;
use App\Domain\Profile\ValueObjects\Profile;
use App\Domain\Profile\ValueObjects\Weight;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EnergyTargetsTest extends TestCase
{
    public function test_it_calculates_mifflin_tdee_and_macros_for_a_male_who_maintains(): void
    {
        $targets = EnergyTargets::for(
            $this->profile(Sex::Male, new Height(180), ActivityLevel::Moderate, Goal::Maintain),
            new Weight(80.0),
            new DateTimeImmutable('2026-11-21'),
        );

        $this->assertSame(1780, $targets->bmrKcal);
        $this->assertSame(2759, $targets->tdeeKcal);
        $this->assertSame(2759, $targets->targetKcal);
        $this->assertSame(160, $targets->proteinGrams);
        $this->assertSame(80, $targets->fatGrams);
        $this->assertSame(350, $targets->carbohydrateGrams);
    }

    public function test_it_floors_a_loss_target_at_bmr_for_a_female(): void
    {
        $targets = EnergyTargets::for(
            $this->profile(Sex::Female, new Height(165), ActivityLevel::Sedentary, Goal::Lose),
            new Weight(65.0),
            new DateTimeImmutable('2026-11-21'),
        );

        $this->assertSame(1370, $targets->bmrKcal);
        $this->assertSame(1644, $targets->tdeeKcal);
        $this->assertSame(1370, $targets->targetKcal);
        $this->assertSame(130, $targets->proteinGrams);
        $this->assertSame(65, $targets->fatGrams);
        $this->assertSame(66, $targets->carbohydrateGrams);
    }

    public function test_it_adds_a_surplus_when_the_goal_is_to_gain(): void
    {
        $targets = EnergyTargets::for(
            $this->profile(Sex::Male, new Height(180), ActivityLevel::Moderate, Goal::Gain),
            new Weight(80.0),
            new DateTimeImmutable('2026-11-21'),
        );

        $this->assertSame(1780, $targets->bmrKcal);
        $this->assertSame(2759, $targets->tdeeKcal);
        $this->assertSame(3059, $targets->targetKcal);
        $this->assertSame(160, $targets->proteinGrams);
        $this->assertSame(80, $targets->fatGrams);
        $this->assertSame(425, $targets->carbohydrateGrams);
    }

    public function test_it_never_returns_negative_carbohydrate_grams(): void
    {
        $targets = EnergyTargets::for(
            $this->profile(
                Sex::Female,
                new Height(150),
                ActivityLevel::Sedentary,
                Goal::Lose,
                new BirthDate(new DateTimeImmutable('1946-11-21')),
            ),
            new Weight(150.0),
            new DateTimeImmutable('2026-11-21'),
        );

        $this->assertGreaterThan(0, $targets->proteinGrams);
        $this->assertGreaterThan(0, $targets->fatGrams);
        $this->assertSame(0, $targets->carbohydrateGrams);
        $this->assertLessThan(
            $targets->proteinGrams * 4 + $targets->fatGrams * 9,
            $targets->targetKcal,
        );
    }

    private function profile(
        Sex $sex,
        Height $height,
        ActivityLevel $activityLevel,
        Goal $goal,
        ?BirthDate $birthDate = null,
    ): Profile {
        return Profile::create(
            new UserId(1),
            $sex,
            $birthDate ?? new BirthDate(new DateTimeImmutable('1996-11-21')),
            $height,
            $activityLevel,
            [],
            $goal,
            SnapshotCadence::Weekly,
        );
    }
}
