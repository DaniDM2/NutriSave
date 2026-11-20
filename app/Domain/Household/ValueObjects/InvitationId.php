<?php

declare(strict_types=1);

namespace App\Domain\Household\ValueObjects;

use App\Domain\Household\Exceptions\InvalidInvitationId;

final readonly class InvitationId
{
    public function __construct(
        public int $value,
    ) {
        if ($value < 1) {
            throw new InvalidInvitationId;
        }
    }
}
