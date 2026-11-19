<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\InvalidUserId;

final readonly class UserId
{
    public function __construct(
        public int $value,
    ) {
        if ($value < 1) {
            throw new InvalidUserId;
        }
    }
}
