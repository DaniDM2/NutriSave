<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Household;

use App\Domain\Household\Exceptions\InvalidHouseholdName;
use App\Domain\Household\ValueObjects\HouseholdName;
use PHPUnit\Framework\TestCase;

class HouseholdNameTest extends TestCase
{
    public function test_it_accepts_a_trimmed_name(): void
    {
        $name = new HouseholdName('  Casa Ada  ');

        $this->assertSame('Casa Ada', $name->value);
    }

    public function test_it_rejects_an_empty_string(): void
    {
        $this->expectException(InvalidHouseholdName::class);

        new HouseholdName('');
    }

    public function test_it_rejects_whitespace_only(): void
    {
        $this->expectException(InvalidHouseholdName::class);

        new HouseholdName('   ');
    }

    public function test_it_rejects_a_name_longer_than_255_characters(): void
    {
        $this->expectException(InvalidHouseholdName::class);

        new HouseholdName(str_repeat('a', 256));
    }

    public function test_it_accepts_a_name_of_255_characters(): void
    {
        $raw = str_repeat('a', 255);

        $name = new HouseholdName($raw);

        $this->assertSame($raw, $name->value);
    }
}
