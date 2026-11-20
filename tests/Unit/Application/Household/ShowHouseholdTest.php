<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Household;

use App\Application\Household\ShowHousehold;
use App\Domain\Household\MemberRole;
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

class ShowHouseholdTest extends TestCase
{
    public function test_it_returns_null_when_the_user_has_no_household(): void
    {
        $view = $this->useCase()->handle(new UserId(1));

        $this->assertNull($view);
    }

    public function test_it_returns_the_household_with_member_names_for_the_owner(): void
    {
        $households = new InMemoryHouseholdRepository;
        $invitations = new InMemoryHouseholdInvitationRepository;
        $users = new InMemoryUserDirectory;
        $ownerId = new UserId(1);
        $memberId = new UserId(2);

        $users->add(new UserRecord($ownerId, 'Ada', 'ada@example.com'));
        $users->add(new UserRecord($memberId, 'Bob', 'bob@example.com'));

        $households->save(
            Household::create(new HouseholdName('Casa Ada'), $ownerId)->addMember($memberId),
        );

        $view = (new ShowHousehold($households, $invitations, $users))->handle($ownerId);

        $this->assertNotNull($view);
        $this->assertSame('Casa Ada', $view->name);
        $this->assertTrue($view->isOwner);
        $this->assertCount(2, $view->members);
        $this->assertSame('Ada', $view->members[0]->name);
        $this->assertSame('ada@example.com', $view->members[0]->email);
        $this->assertSame(MemberRole::Owner, $view->members[0]->role);
        $this->assertSame('Bob', $view->members[1]->name);
        $this->assertSame(MemberRole::Member, $view->members[1]->role);
        $this->assertSame([], $view->pendingInvitations);
    }

    public function test_it_includes_pending_invitations_only_for_the_owner(): void
    {
        $households = new InMemoryHouseholdRepository;
        $invitations = new InMemoryHouseholdInvitationRepository;
        $users = new InMemoryUserDirectory;
        $ownerId = new UserId(1);
        $memberId = new UserId(2);

        $users->add(new UserRecord($ownerId, 'Ada', 'ada@example.com'));
        $users->add(new UserRecord($memberId, 'Bob', 'bob@example.com'));

        $saved = $households->save(
            Household::create(new HouseholdName('Casa Ada'), $ownerId)->addMember($memberId),
        );
        $this->assertNotNull($saved->id);

        $expiresAt = new DateTimeImmutable('+7 days');
        $invitations->save(new HouseholdInvitation(
            $saved->id,
            'carol@example.com',
            $ownerId,
            HouseholdInvitation::hashToken('plain-token'),
            $expiresAt,
        ));

        $ownerView = (new ShowHousehold($households, $invitations, $users))->handle($ownerId);
        $memberView = (new ShowHousehold($households, $invitations, $users))->handle($memberId);

        $this->assertNotNull($ownerView);
        $this->assertCount(1, $ownerView->pendingInvitations);
        $this->assertSame('carol@example.com', $ownerView->pendingInvitations[0]->email);
        $this->assertEquals($expiresAt, $ownerView->pendingInvitations[0]->expiresAt);

        $this->assertNotNull($memberView);
        $this->assertFalse($memberView->isOwner);
        $this->assertSame([], $memberView->pendingInvitations);
    }

    private function useCase(): ShowHousehold
    {
        return new ShowHousehold(
            new InMemoryHouseholdRepository,
            new InMemoryHouseholdInvitationRepository,
            new InMemoryUserDirectory,
        );
    }
}
