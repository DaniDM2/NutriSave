<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Household;

use App\Application\Household\InviteHouseholdMember;
use App\Domain\Household\Exceptions\CannotInviteSelf;
use App\Domain\Household\Exceptions\DuplicateHouseholdMember;
use App\Domain\Household\Exceptions\InvalidInvitationEmail;
use App\Domain\Household\Exceptions\NotHouseholdOwner;
use App\Domain\Household\Exceptions\UserAlreadyInHousehold;
use App\Domain\Household\ValueObjects\Household;
use App\Domain\Household\ValueObjects\HouseholdInvitation;
use App\Domain\Household\ValueObjects\HouseholdName;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\ValueObjects\UserRecord;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\InMemoryHouseholdInvitationRepository;
use Tests\Fakes\InMemoryHouseholdRepository;
use Tests\Fakes\InMemoryUserDirectory;
use Tests\Fakes\RecordingHouseholdInvitationNotifier;

class InviteHouseholdMemberTest extends TestCase
{
    public function test_it_invites_an_email_that_has_no_account(): void
    {
        [$invite, $households, $invitations, $notifier, $ownerId] = $this->ownerSetup();

        $invite->handle($ownerId, '  Carol@Example.com ');

        $this->assertCount(1, $notifier->sent);
        $this->assertSame('carol@example.com', $notifier->sent[0]['email']);
        $this->assertSame('Casa Ada', $notifier->sent[0]['householdName']);
        $this->assertNotSame('', $notifier->sent[0]['plainToken']);

        $household = $households->findByUserId($ownerId);
        $this->assertNotNull($household?->id);
        $pending = $invitations->findPendingByHouseholdAndEmail($household->id, 'carol@example.com');
        $this->assertNotNull($pending);
        $this->assertSame(
            HouseholdInvitation::hashToken($notifier->sent[0]['plainToken']),
            $pending->tokenHash,
        );
        $this->assertFalse($pending->isExpired());
    }

    public function test_it_resends_by_replacing_the_token_of_a_pending_invitation(): void
    {
        [$invite, $households, $invitations, $notifier, $ownerId] = $this->ownerSetup();

        $invite->handle($ownerId, 'carol@example.com');
        $firstToken = $notifier->sent[0]['plainToken'];
        $household = $households->findByUserId($ownerId);
        $this->assertNotNull($household?->id);
        $first = $invitations->findPendingByHouseholdAndEmail($household->id, 'carol@example.com');
        $this->assertNotNull($first?->id);

        $invite->handle($ownerId, 'carol@example.com');

        $this->assertCount(2, $notifier->sent);
        $this->assertNotSame($firstToken, $notifier->sent[1]['plainToken']);
        $this->assertCount(1, $invitations->findPendingByHousehold($household->id));
        $updated = $invitations->findPendingByHouseholdAndEmail($household->id, 'carol@example.com');
        $this->assertNotNull($updated);
        $this->assertSame($first->id->value, $updated->id?->value);
        $this->assertSame(
            HouseholdInvitation::hashToken($notifier->sent[1]['plainToken']),
            $updated->tokenHash,
        );
    }

    public function test_it_rejects_a_member_who_is_not_the_owner(): void
    {
        [$invite, , , , $ownerId, $memberId] = $this->ownerSetup(withMember: true);

        $this->expectException(NotHouseholdOwner::class);

        $invite->handle($memberId, 'carol@example.com');
    }

    public function test_it_rejects_inviting_the_owner_email(): void
    {
        [$invite, , , , $ownerId] = $this->ownerSetup();

        $this->expectException(CannotInviteSelf::class);

        $invite->handle($ownerId, 'ADA@example.com');
    }

    public function test_it_rejects_inviting_an_existing_member(): void
    {
        [$invite, , , , $ownerId] = $this->ownerSetup(withMember: true);

        $this->expectException(DuplicateHouseholdMember::class);

        $invite->handle($ownerId, 'bob@example.com');
    }

    public function test_it_rejects_inviting_a_user_who_already_has_a_household(): void
    {
        $households = new InMemoryHouseholdRepository;
        $invitations = new InMemoryHouseholdInvitationRepository;
        $users = new InMemoryUserDirectory;
        $notifier = new RecordingHouseholdInvitationNotifier;
        $ownerId = new UserId(1);
        $otherOwnerId = new UserId(3);

        $users->add(new UserRecord($ownerId, 'Ada', 'ada@example.com'));
        $users->add(new UserRecord($otherOwnerId, 'Eve', 'eve@example.com'));
        $households->save(Household::create(new HouseholdName('Casa Ada'), $ownerId));
        $households->save(Household::create(new HouseholdName('Casa Eve'), $otherOwnerId));

        $this->expectException(UserAlreadyInHousehold::class);

        (new InviteHouseholdMember($households, $invitations, $users, $notifier))
            ->handle($ownerId, 'eve@example.com');
    }

    public function test_it_rejects_an_invalid_email(): void
    {
        [$invite, , , , $ownerId] = $this->ownerSetup();

        $this->expectException(InvalidInvitationEmail::class);

        $invite->handle($ownerId, 'not-an-email');
    }

    /**
     * @return array{
     *     0: InviteHouseholdMember,
     *     1: InMemoryHouseholdRepository,
     *     2: InMemoryHouseholdInvitationRepository,
     *     3: RecordingHouseholdInvitationNotifier,
     *     4: UserId,
     *     5: UserId
     * }
     */
    private function ownerSetup(bool $withMember = false): array
    {
        $households = new InMemoryHouseholdRepository;
        $invitations = new InMemoryHouseholdInvitationRepository;
        $users = new InMemoryUserDirectory;
        $notifier = new RecordingHouseholdInvitationNotifier;
        $ownerId = new UserId(1);
        $memberId = new UserId(2);

        $users->add(new UserRecord($ownerId, 'Ada', 'ada@example.com'));
        $users->add(new UserRecord($memberId, 'Bob', 'bob@example.com'));

        $household = Household::create(new HouseholdName('Casa Ada'), $ownerId);

        if ($withMember) {
            $household = $household->addMember($memberId);
        }

        $households->save($household);

        return [
            new InviteHouseholdMember($households, $invitations, $users, $notifier),
            $households,
            $invitations,
            $notifier,
            $ownerId,
            $memberId,
        ];
    }
}
