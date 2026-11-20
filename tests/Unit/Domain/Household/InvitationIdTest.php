<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Household;

use App\Domain\Household\Exceptions\InvalidInvitationId;
use App\Domain\Household\ValueObjects\InvitationId;
use PHPUnit\Framework\TestCase;

class InvitationIdTest extends TestCase
{
    public function test_it_accepts_a_positive_integer(): void
    {
        $id = new InvitationId(1);

        $this->assertSame(1, $id->value);
    }

    public function test_it_rejects_zero(): void
    {
        $this->expectException(InvalidInvitationId::class);

        new InvitationId(0);
    }

    public function test_it_rejects_a_negative_integer(): void
    {
        $this->expectException(InvalidInvitationId::class);

        new InvitationId(-1);
    }
}
