<?php

declare(strict_types=1);

namespace App\Application\Household;

use DateTimeImmutable;

final readonly class PendingInvitationView
{
    public function __construct(
        public string $email,
        public DateTimeImmutable $expiresAt,
    ) {}
}
