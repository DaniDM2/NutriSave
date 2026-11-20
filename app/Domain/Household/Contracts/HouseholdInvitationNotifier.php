<?php

declare(strict_types=1);

namespace App\Domain\Household\Contracts;

interface HouseholdInvitationNotifier
{
    public function notify(string $email, string $householdName, string $plainToken): void;
}
