<?php

declare(strict_types=1);

namespace App\Application\Household;

use App\Domain\Household\ValueObjects\HouseholdId;

final readonly class HouseholdView
{
    /**
     * @param  list<HouseholdMemberView>  $members
     * @param  list<PendingInvitationView>  $pendingInvitations
     */
    public function __construct(
        public HouseholdId $id,
        public string $name,
        public array $members,
        public array $pendingInvitations,
        public bool $isOwner,
    ) {}
}
