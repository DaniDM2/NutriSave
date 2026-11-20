<?php

declare(strict_types=1);

namespace App\Application\Household;

final readonly class InvitationPreview
{
    public function __construct(
        public string $householdName,
        public string $email,
        public bool $expired,
    ) {}
}
