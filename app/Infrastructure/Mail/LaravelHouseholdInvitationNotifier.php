<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail;

use App\Domain\Household\Contracts\HouseholdInvitationNotifier;
use Illuminate\Support\Facades\Notification;

final class LaravelHouseholdInvitationNotifier implements HouseholdInvitationNotifier
{
    public function notify(string $email, string $householdName, string $plainToken): void
    {
        Notification::route('mail', $email)
            ->notify(new HouseholdInvitationNotification($householdName, $plainToken));
    }
}
