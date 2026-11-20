<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Household;

use App\Application\Household\AcceptHouseholdInvitation;
use App\Domain\Household\Exceptions\InvitationEmailMismatch;
use App\Domain\Household\Exceptions\InvitationExpired;
use App\Domain\Household\Exceptions\InvitationNotFound;
use App\Domain\Household\Exceptions\UserAlreadyInHousehold;
use App\Domain\Household\ValueObjects\Household;
use App\Domain\Household\ValueObjects\HouseholdInvitation;
use App\Domain\Household\ValueObjects\HouseholdName;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\ValueObjects\UserRecord;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\InMemoryHouseholdInvitationRepository;
use Tests\Fakes\InMemoryHouseholdRepository;
use Tests\Fakes\InMemoryUserDirectory;

class AcceptHouseholdInvitationTest extends TestCase
{
    public function test_it_adds_the_user_and_deletes_the_invitation(): void
    {
        [$accept, $households, $invitations, $inviteeId] = $this->pendingInvitation();

        $accept->handle($inviteeId, 'plain-token');

        $household = $households->findByUserId($inviteeId);
        $this->assertNotNull($household);
        $this->assertTrue($household->hasMember($inviteeId));
        $this->assertFalse($household->isOwner($inviteeId));
        $this->assertNull($invitations->findByTokenHash(HouseholdInvitation::hashToken('plain-token')));
    }

    public function test_it_rejects_an_unknown_token(): void
    {
        [$accept, , , $inviteeId] = $this->pendingInvitation();

        $this->expectException(InvitationNotFound::class);

        $accept->handle($inviteeId, 'wrong-token');
    }

    public function test_it_rejects_an_expired_invitation(): void
    {
        [$accept, , , $inviteeId] = $this->pendingInvitation(expired: true);

        $this->expectException(InvitationExpired::class);

        $accept->handle($inviteeId, 'plain-token');
    }

    public function test_it_rejects_when_the_session_email_does_not_match(): void
    {
        [$accept] = $this->pendingInvitation();

        $this->expectException(InvitationEmailMismatch::class);

        $accept->handle(new UserId(9), 'plain-token');
    }

    public function test_it_rejects_when_the_user_already_has_a_household(): void
    {
        [$accept, $households, , $inviteeId] = $this->pendingInvitation();
        $households->save(Household::create(new HouseholdName('Casa Carol'), $inviteeId));

        $this->expectException(UserAlreadyInHousehold::class);

        $accept->handle($inviteeId, 'plain-token');
    }

    /**
     * @return array{
     *     0: AcceptHouseholdInvitation,
     *     1: InMemoryHouseholdRepository,
     *     2: InMemoryHouseholdInvitationRepository,
     *     3: UserId
     * }
     */
    private function pendingInvitation(bool $expired = false): array
    {
        $households = new InMemoryHouseholdRepository;
        $invitations = new InMemoryHouseholdInvitationRepository;
        $users = new InMemoryUserDirectory;
        $ownerId = new UserId(1);
        $inviteeId = new UserId(2);

        $users->add(new UserRecord($ownerId, 'Ada', 'ada@example.com'));
        $users->add(new UserRecord($inviteeId, 'Carol', 'carol@example.com'));

        $household = $households->save(Household::create(new HouseholdName('Casa Ada'), $ownerId));
        $this->assertNotNull($household->id);

        $invitations->save(new HouseholdInvitation(
            $household->id,
            'carol@example.com',
            $ownerId,
            HouseholdInvitation::hashToken('plain-token'),
            $expired ? new DateTimeImmutable('-1 hour') : new DateTimeImmutable('+7 days'),
        ));

        return [
            new AcceptHouseholdInvitation($households, $invitations, $users),
            $households,
            $invitations,
            $inviteeId,
        ];
    }
}
