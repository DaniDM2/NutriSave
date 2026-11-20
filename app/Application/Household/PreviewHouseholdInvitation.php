<?php

declare(strict_types=1);

namespace App\Application\Household;

use App\Domain\Household\Contracts\HouseholdInvitationRepository;
use App\Domain\Household\Contracts\HouseholdRepository;
use App\Domain\Household\Exceptions\InvitationNotFound;
use App\Domain\Household\ValueObjects\HouseholdInvitation;

final readonly class PreviewHouseholdInvitation
{
    public function __construct(
        private HouseholdRepository $households,
        private HouseholdInvitationRepository $invitations,
    ) {}

    public function handle(string $plainToken): InvitationPreview
    {
        $invitation = $this->invitations->findByTokenHash(HouseholdInvitation::hashToken($plainToken));

        if ($invitation === null) {
            throw new InvitationNotFound;
        }

        $household = $this->households->findById($invitation->householdId);

        if ($household === null) {
            throw new InvitationNotFound;
        }

        return new InvitationPreview(
            $household->name->value,
            $invitation->email,
            $invitation->isExpired(),
        );
    }
}
