<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Profile;

use App\Domain\Profile\Exceptions\InvalidProfileId;
use App\Domain\Profile\ValueObjects\ProfileId;
use PHPUnit\Framework\TestCase;

class ProfileIdTest extends TestCase
{
    public function test_it_accepts_a_positive_integer(): void
    {
        $id = new ProfileId(1);

        $this->assertSame(1, $id->value);
    }

    public function test_it_rejects_zero(): void
    {
        $this->expectException(InvalidProfileId::class);

        new ProfileId(0);
    }

    public function test_it_rejects_a_negative_integer(): void
    {
        $this->expectException(InvalidProfileId::class);

        new ProfileId(-1);
    }
}
