<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Household;

use App\Domain\Household\Exceptions\InvalidHouseholdId;
use App\Domain\Household\ValueObjects\HouseholdId;
use PHPUnit\Framework\TestCase;

class HouseholdIdTest extends TestCase
{
    public function test_it_accepts_a_positive_integer(): void
    {
        $id = new HouseholdId(1);

        $this->assertSame(1, $id->value);
    }

    public function test_it_rejects_zero(): void
    {
        $this->expectException(InvalidHouseholdId::class);

        new HouseholdId(0);
    }

    public function test_it_rejects_a_negative_integer(): void
    {
        $this->expectException(InvalidHouseholdId::class);

        new HouseholdId(-1);
    }
}
