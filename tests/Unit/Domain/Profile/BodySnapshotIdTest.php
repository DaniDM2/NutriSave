<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Profile;

use App\Domain\Profile\Exceptions\InvalidBodySnapshotId;
use App\Domain\Profile\ValueObjects\BodySnapshotId;
use PHPUnit\Framework\TestCase;

class BodySnapshotIdTest extends TestCase
{
    public function test_it_accepts_a_positive_integer(): void
    {
        $id = new BodySnapshotId(1);

        $this->assertSame(1, $id->value);
    }

    public function test_it_rejects_zero(): void
    {
        $this->expectException(InvalidBodySnapshotId::class);

        new BodySnapshotId(0);
    }

    public function test_it_rejects_a_negative_integer(): void
    {
        $this->expectException(InvalidBodySnapshotId::class);

        new BodySnapshotId(-1);
    }
}
