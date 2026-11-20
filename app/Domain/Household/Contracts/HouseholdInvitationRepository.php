<?php

declare(strict_types=1);

namespace App\Domain\Household\Contracts;

use App\Domain\Household\ValueObjects\HouseholdId;
use App\Domain\Household\ValueObjects\HouseholdInvitation;

interface HouseholdInvitationRepository
{
    public function save(HouseholdInvitation $invitation): HouseholdInvitation;

    public function findByTokenHash(string $tokenHash): ?HouseholdInvitation;

    /**
     * @return list<HouseholdInvitation>
     */
    public function findPendingByHousehold(HouseholdId $householdId): array;

    public function findPendingByHouseholdAndEmail(HouseholdId $householdId, string $email): ?HouseholdInvitation;

    public function delete(HouseholdInvitation $invitation): void;
}
