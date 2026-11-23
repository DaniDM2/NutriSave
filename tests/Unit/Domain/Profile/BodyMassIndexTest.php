<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Profile;

use App\Domain\Profile\Exceptions\InvalidBodyMassIndex;
use App\Domain\Profile\ValueObjects\BodyMassIndex;
use App\Domain\Profile\ValueObjects\Height;
use App\Domain\Profile\ValueObjects\Weight;
use PHPUnit\Framework\TestCase;

class BodyMassIndexTest extends TestCase
{
    public function test_it_calculates_bmi_to_one_decimal(): void
    {
        $bmi = BodyMassIndex::from(new Height(180), new Weight(80.0));

        $this->assertSame(24.7, $bmi->value);
    }

    public function test_it_rehydrates_a_persisted_value_rounded_to_one_decimal(): void
    {
        $bmi = BodyMassIndex::of(24.74);

        $this->assertSame(24.7, $bmi->value);
    }

    public function test_it_rejects_a_non_positive_persisted_value(): void
    {
        $this->expectException(InvalidBodyMassIndex::class);

        BodyMassIndex::of(0.0);
    }
}
