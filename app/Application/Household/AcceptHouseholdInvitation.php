<?php

declare(strict_types=1);

namespace App\Application\Household;

use App\Domain\Household\Contracts\HouseholdInvitationRepository;
use App\Domain\Household\Contracts\HouseholdRepository;
use App\Domain\Household\Exceptions\InvitationEmailMismatch;
use App\Domain\Household\Exceptions\InvitationExpired;
use App\Domain\Household\Exceptions\InvitationNotFound;
use App\Domain\Household\Exceptions\UserAlreadyInHousehold;
use App\Domain\Household\ValueObjects\HouseholdInvitation;
use App\Domain\Shared\Contracts\UserDirectory;
use App\Domain\Shared\ValueObjects\UserId;

final readonly class AcceptHouseholdInvitation
{
    public function __construct(
        private HouseholdRepository $households,
        private HouseholdInvitationRepository $invitations,
        private UserDirectory $users,
    ) {}

    public function handle(UserId $userId, string $plainToken): void
    {
        $invitation = $this->invitations->findByTokenHash(HouseholdInvitation::hashToken($plainToken));

        if ($invitation === null) {
            throw new InvitationNotFound;
        }

        if ($invitation->isExpired()) {
            throw new InvitationExpired;
        }

        $record = $this->users->findByIds([$userId])[$userId->value] ?? null;

        if ($record === null || strtolower($record->email) !== $invitation->email) {
            throw new InvitationEmailMismatch;
        }

        if ($this->households->findByUserId($userId) !== null) {
            throw new UserAlreadyInHousehold;
        }

        $household = $this->households->findById($invitation->householdId);

        if ($household === null) {
            throw new InvitationNotFound;
        }

        $this->households->save($household->addMember($userId));
        $this->invitations->delete($invitation);
    }
}
