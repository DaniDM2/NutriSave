<?php

declare(strict_types=1);

namespace App\Application\Household;

use App\Domain\Household\Contracts\HouseholdInvitationNotifier;
use App\Domain\Household\Contracts\HouseholdInvitationRepository;
use App\Domain\Household\Contracts\HouseholdRepository;
use App\Domain\Household\Exceptions\CannotInviteSelf;
use App\Domain\Household\Exceptions\DuplicateHouseholdMember;
use App\Domain\Household\Exceptions\NotHouseholdOwner;
use App\Domain\Household\Exceptions\UserAlreadyInHousehold;
use App\Domain\Household\ValueObjects\HouseholdInvitation;
use App\Domain\Shared\Contracts\UserDirectory;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;

final readonly class InviteHouseholdMember
{
    public function __construct(
        private HouseholdRepository $households,
        private HouseholdInvitationRepository $invitations,
        private UserDirectory $users,
        private HouseholdInvitationNotifier $notifier,
    ) {}

    public function handle(UserId $actorId, string $email): void
    {
        $household = $this->households->findByUserId($actorId);

        if ($household === null || $household->id === null || ! $household->isOwner($actorId)) {
            throw new NotHouseholdOwner;
        }

        $plainToken = bin2hex(random_bytes(32));
        $invitation = new HouseholdInvitation(
            $household->id,
            $email,
            $actorId,
            HouseholdInvitation::hashToken($plainToken),
            new DateTimeImmutable('+7 days'),
        );

        $actor = $this->users->findByIds([$actorId])[$actorId->value] ?? null;

        if ($actor !== null && strtolower($actor->email) === $invitation->email) {
            throw new CannotInviteSelf;
        }

        $existingUser = $this->users->findByEmail($invitation->email);

        if ($existingUser !== null) {
            if ($household->hasMember($existingUser->id)) {
                throw new DuplicateHouseholdMember;
            }

            if ($this->households->findByUserId($existingUser->id) !== null) {
                throw new UserAlreadyInHousehold;
            }
        }

        $pending = $this->invitations->findPendingByHouseholdAndEmail($household->id, $invitation->email);

        if ($pending?->id !== null) {
            $invitation = $invitation->withId($pending->id);
        }

        $this->invitations->save($invitation);
        $this->notifier->notify($invitation->email, $household->name->value, $plainToken);
    }
}
