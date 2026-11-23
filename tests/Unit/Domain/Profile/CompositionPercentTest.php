<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Profile;

use App\Domain\Profile\Exceptions\InvalidCompositionPercent;
use App\Domain\Profile\ValueObjects\CompositionPercent;
use PHPUnit\Framework\TestCase;

class CompositionPercentTest extends TestCase
{
    public function test_it_accepts_a_percent_and_rounds_to_one_decimal(): void
    {
        $percent = new CompositionPercent(18.34);

        $this->assertSame(18.3, $percent->value);
    }

    public function test_it_accepts_zero(): void
    {
        $percent = new CompositionPercent(0.0);

        $this->assertSame(0.0, $percent->value);
    }

    public function test_it_accepts_one_hundred(): void
    {
        $percent = new CompositionPercent(100.0);

        $this->assertSame(100.0, $percent->value);
    }

    public function test_it_rejects_a_negative_percent(): void
    {
        $this->expectException(InvalidCompositionPercent::class);

        new CompositionPercent(-0.1);
    }

    public function test_it_rejects_a_percent_above_100(): void
    {
        $this->expectException(InvalidCompositionPercent::class);

        new CompositionPercent(100.1);
    }
}
