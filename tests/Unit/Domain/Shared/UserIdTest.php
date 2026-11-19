<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Shared;

use App\Domain\Shared\Exceptions\InvalidUserId;
use App\Domain\Shared\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class UserIdTest extends TestCase
{
    public function test_it_accepts_a_positive_integer(): void
    {
        $id = new UserId(1);

        $this->assertSame(1, $id->value);
    }

    public function test_it_rejects_zero(): void
    {
        $this->expectException(InvalidUserId::class);

        new UserId(0);
    }

    public function test_it_rejects_a_negative_integer(): void
    {
        $this->expectException(InvalidUserId::class);

        new UserId(-1);
    }
}
