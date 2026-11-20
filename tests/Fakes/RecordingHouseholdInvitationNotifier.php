<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Domain\Household\Contracts\HouseholdInvitationNotifier;

final class RecordingHouseholdInvitationNotifier implements HouseholdInvitationNotifier
{
    /** @var list<array{email: string, householdName: string, plainToken: string}> */
    public array $sent = [];

    public function notify(string $email, string $householdName, string $plainToken): void
    {
        $this->sent[] = [
            'email' => $email,
            'householdName' => $householdName,
            'plainToken' => $plainToken,
        ];
    }
}
