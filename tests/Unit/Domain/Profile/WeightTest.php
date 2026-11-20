<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Profile;

use App\Domain\Profile\Exceptions\InvalidWeight;
use App\Domain\Profile\ValueObjects\Weight;
use PHPUnit\Framework\TestCase;

class WeightTest extends TestCase
{
    public function test_it_accepts_weight_in_kilograms(): void
    {
        $weight = new Weight(80.0);

        $this->assertSame(80.0, $weight->kilograms);
    }

    public function test_it_accepts_the_minimum_weight(): void
    {
        $weight = new Weight(20.0);

        $this->assertSame(20.0, $weight->kilograms);
    }

    public function test_it_accepts_the_maximum_weight(): void
    {
        $weight = new Weight(400.0);

        $this->assertSame(400.0, $weight->kilograms);
    }

    public function test_it_rejects_weight_below_20_kg(): void
    {
        $this->expectException(InvalidWeight::class);

        new Weight(19.99);
    }

    public function test_it_rejects_weight_above_400_kg(): void
    {
        $this->expectException(InvalidWeight::class);

        new Weight(400.01);
    }
}
