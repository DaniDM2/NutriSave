<?php

declare(strict_types=1);

namespace App\Application\Household;

use App\Domain\Household\Contracts\HouseholdInvitationRepository;
use App\Domain\Household\Contracts\HouseholdRepository;
use App\Domain\Household\ValueObjects\HouseholdInvitation;
use App\Domain\Household\ValueObjects\HouseholdMember;
use App\Domain\Shared\Contracts\UserDirectory;
use App\Domain\Shared\ValueObjects\UserId;

final readonly class ShowHousehold
{
    public function __construct(
        private HouseholdRepository $households,
        private HouseholdInvitationRepository $invitations,
        private UserDirectory $users,
    ) {}

    public function handle(UserId $userId): ?HouseholdView
    {
        $household = $this->households->findByUserId($userId);

        if ($household === null || $household->id === null) {
            return null;
        }

        $records = $this->users->findByIds(
            array_map(
                fn (HouseholdMember $member): UserId => $member->userId,
                $household->members,
            ),
        );

        $members = [];

        foreach ($household->members as $member) {
            $record = $records[$member->userId->value] ?? null;
            $members[] = new HouseholdMemberView(
                $member->userId,
                $record?->name ?? '',
                $record?->email ?? '',
                $member->role,
            );
        }

        $isOwner = $household->isOwner($userId);
        $pending = [];

        if ($isOwner) {
            $pending = array_map(
                fn (HouseholdInvitation $invitation): PendingInvitationView => new PendingInvitationView(
                    $invitation->email,
                    $invitation->expiresAt,
                ),
                $this->invitations->findPendingByHousehold($household->id),
            );
        }

        return new HouseholdView(
            $household->id,
            $household->name->value,
            $members,
            $pending,
            $isOwner,
        );
    }
}
