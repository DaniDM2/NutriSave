<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Profile;

use App\Domain\Profile\Exceptions\InvalidBirthDate;
use App\Domain\Profile\ValueObjects\BirthDate;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class BirthDateTest extends TestCase
{
    public function test_it_accepts_a_date_from_1900(): void
    {
        $date = new BirthDate(new DateTimeImmutable('1900-01-01 15:30:00'));

        $this->assertSame('1900-01-01', $date->value->format('Y-m-d'));
        $this->assertSame('00:00:00', $date->value->format('H:i:s'));
    }

    public function test_it_rejects_a_year_before_1900(): void
    {
        $this->expectException(InvalidBirthDate::class);

        new BirthDate(new DateTimeImmutable('1899-12-31'));
    }
}
