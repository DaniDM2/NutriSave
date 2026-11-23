<?php

declare(strict_types=1);

namespace App\Domain\Profile\ValueObjects;

use App\Domain\Profile\Exceptions\InvalidBodySnapshotId;

final readonly class BodySnapshotId
{
    public function __construct(
        public int $value,
    ) {
        if ($value < 1) {
            throw new InvalidBodySnapshotId;
        }
    }
}
