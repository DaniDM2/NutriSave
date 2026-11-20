<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Domain\Household\Contracts\HouseholdInvitationRepository;
use App\Domain\Household\ValueObjects\HouseholdId;
use App\Domain\Household\ValueObjects\HouseholdInvitation;
use App\Domain\Household\ValueObjects\InvitationId;

final class InMemoryHouseholdInvitationRepository implements HouseholdInvitationRepository
{
    /** @var array<int, HouseholdInvitation> */
    private array $invitations = [];

    private int $nextId = 1;

    public function save(HouseholdInvitation $invitation): HouseholdInvitation
    {
        if ($invitation->id === null) {
            $invitation = $invitation->withId(new InvitationId($this->nextId));
            $this->nextId++;
        }

        $this->invitations[$invitation->id->value] = $invitation;

        return $invitation;
    }

    public function findByTokenHash(string $tokenHash): ?HouseholdInvitation
    {
        foreach ($this->invitations as $invitation) {
            if ($invitation->tokenHash === $tokenHash) {
                return $invitation;
            }
        }

        return null;
    }

    public function findPendingByHousehold(HouseholdId $householdId): array
    {
        $pending = [];

        foreach ($this->invitations as $invitation) {
            if ($invitation->householdId->value === $householdId->value) {
                $pending[] = $invitation;
            }
        }

        return array_values($pending);
    }

    public function findPendingByHouseholdAndEmail(HouseholdId $householdId, string $email): ?HouseholdInvitation
    {
        $normalized = strtolower(trim($email));

        foreach ($this->invitations as $invitation) {
            if (
                $invitation->householdId->value === $householdId->value
                && $invitation->email === $normalized
            ) {
                return $invitation;
            }
        }

        return null;
    }

    public function delete(HouseholdInvitation $invitation): void
    {
        if ($invitation->id === null) {
            return;
        }

        unset($this->invitations[$invitation->id->value]);
    }
}
