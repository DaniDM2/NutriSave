<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Profile;

use App\Domain\Profile\Exceptions\InvalidSportName;
use App\Domain\Profile\ValueObjects\SportName;
use PHPUnit\Framework\TestCase;

class SportNameTest extends TestCase
{
    public function test_it_accepts_a_trimmed_name(): void
    {
        $name = new SportName('  Running  ');

        $this->assertSame('Running', $name->value);
    }

    public function test_it_rejects_an_empty_string(): void
    {
        $this->expectException(InvalidSportName::class);

        new SportName('');
    }

    public function test_it_rejects_whitespace_only(): void
    {
        $this->expectException(InvalidSportName::class);

        new SportName('   ');
    }

    public function test_it_rejects_a_name_longer_than_50_characters(): void
    {
        $this->expectException(InvalidSportName::class);

        new SportName(str_repeat('a', 51));
    }

    public function test_it_accepts_a_name_of_50_characters(): void
    {
        $raw = str_repeat('a', 50);

        $name = new SportName($raw);

        $this->assertSame($raw, $name->value);
    }
}
