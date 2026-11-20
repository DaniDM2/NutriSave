<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Profile;

use App\Domain\Profile\Exceptions\InvalidHeight;
use App\Domain\Profile\ValueObjects\Height;
use PHPUnit\Framework\TestCase;

class HeightTest extends TestCase
{
    public function test_it_accepts_height_in_centimeters(): void
    {
        $height = new Height(180);

        $this->assertSame(180, $height->centimeters);
    }

    public function test_it_accepts_the_minimum_height(): void
    {
        $height = new Height(50);

        $this->assertSame(50, $height->centimeters);
    }

    public function test_it_accepts_the_maximum_height(): void
    {
        $height = new Height(250);

        $this->assertSame(250, $height->centimeters);
    }

    public function test_it_rejects_height_below_50_cm(): void
    {
        $this->expectException(InvalidHeight::class);

        new Height(49);
    }

    public function test_it_rejects_height_above_250_cm(): void
    {
        $this->expectException(InvalidHeight::class);

        new Height(251);
    }
}
