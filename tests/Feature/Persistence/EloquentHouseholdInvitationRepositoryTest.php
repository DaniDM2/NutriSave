<?php

declare(strict_types=1);

namespace Tests\Feature\Persistence;

use App\Domain\Household\ValueObjects\Household;
use App\Domain\Household\ValueObjects\HouseholdId;
use App\Domain\Household\ValueObjects\HouseholdInvitation;
use App\Domain\Household\ValueObjects\HouseholdName;
use App\Domain\Shared\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\EloquentHouseholdInvitationRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentHouseholdRepository;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentHouseholdInvitationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_and_finds_an_invitation_by_token_hash(): void
    {
        [$householdId, $ownerId] = $this->household();
        $expiresAt = new DateTimeImmutable('+7 days');
        $tokenHash = HouseholdInvitation::hashToken('plain-token');

        $saved = $this->invitations()->save(new HouseholdInvitation(
            $householdId,
            '  Carol@Example.com ',
            $ownerId,
            $tokenHash,
            $expiresAt,
        ));

        $this->assertNotNull($saved->id);
        $this->assertSame('carol@example.com', $saved->email);
        $this->assertDatabaseHas('household_invitations', [
            'id' => $saved->id->value,
            'household_id' => $householdId->value,
            'email' => 'carol@example.com',
            'token_hash' => $tokenHash,
            'invited_by' => $ownerId->value,
        ]);

        $found = $this->invitations()->findByTokenHash($tokenHash);

        $this->assertNotNull($found);
        $this->assertSame($saved->id?->value, $found->id?->value);
        $this->assertSame('carol@example.com', $found->email);
        $this->assertEquals($expiresAt->getTimestamp(), $found->expiresAt->getTimestamp());
    }

    public function test_it_returns_null_for_an_unknown_token_hash(): void
    {
        $this->assertNull($this->invitations()->findByTokenHash('missing'));
    }

    public function test_it_finds_pending_invitations_by_household_and_email(): void
    {
        [$householdId, $ownerId] = $this->household();
        $this->invitations()->save(new HouseholdInvitation(
            $householdId,
            'carol@example.com',
            $ownerId,
            HouseholdInvitation::hashToken('one'),
            new DateTimeImmutable('+7 days'),
        ));
        $this->invitations()->save(new HouseholdInvitation(
            $householdId,
            'dan@example.com',
            $ownerId,
            HouseholdInvitation::hashToken('two'),
            new DateTimeImmutable('+7 days'),
        ));

        $pending = $this->invitations()->findPendingByHousehold($householdId);
        $byEmail = $this->invitations()->findPendingByHouseholdAndEmail($householdId, 'Carol@Example.com');

        $this->assertCount(2, $pending);
        $this->assertSame('carol@example.com', $pending[0]->email);
        $this->assertSame('dan@example.com', $pending[1]->email);
        $this->assertNotNull($byEmail);
        $this->assertSame('carol@example.com', $byEmail->email);
    }

    public function test_it_updates_an_existing_invitation_token(): void
    {
        [$householdId, $ownerId] = $this->household();
        $saved = $this->invitations()->save(new HouseholdInvitation(
            $householdId,
            'carol@example.com',
            $ownerId,
            HouseholdInvitation::hashToken('old'),
            new DateTimeImmutable('+1 day'),
        ));
        $this->assertNotNull($saved->id);

        $updatedHash = HouseholdInvitation::hashToken('new');
        $newExpiry = new DateTimeImmutable('+7 days');
        $this->invitations()->save(new HouseholdInvitation(
            $householdId,
            'carol@example.com',
            $ownerId,
            $updatedHash,
            $newExpiry,
            $saved->id,
        ));

        $this->assertDatabaseCount('household_invitations', 1);
        $found = $this->invitations()->findByTokenHash($updatedHash);
        $this->assertNotNull($found);
        $this->assertSame($saved->id->value, $found->id?->value);
        $this->assertEquals($newExpiry->getTimestamp(), $found->expiresAt->getTimestamp());
    }

    public function test_it_deletes_an_invitation(): void
    {
        [$householdId, $ownerId] = $this->household();
        $saved = $this->invitations()->save(new HouseholdInvitation(
            $householdId,
            'carol@example.com',
            $ownerId,
            HouseholdInvitation::hashToken('plain-token'),
            new DateTimeImmutable('+7 days'),
        ));
        $this->assertNotNull($saved->id);

        $this->invitations()->delete($saved);

        $this->assertDatabaseCount('household_invitations', 0);
        $this->assertNull($this->invitations()->findByTokenHash(HouseholdInvitation::hashToken('plain-token')));
    }

    /**
     * @return array{0: HouseholdId, 1: UserId}
     */
    private function household(): array
    {
        $owner = User::factory()->create();
        $ownerId = new UserId((int) $owner->id);
        $saved = (new EloquentHouseholdRepository)->save(
            Household::create(new HouseholdName('Casa Ada'), $ownerId),
        );

        $this->assertNotNull($saved->id);

        return [$saved->id, $ownerId];
    }

    private function invitations(): EloquentHouseholdInvitationRepository
    {
        return new EloquentHouseholdInvitationRepository;
    }
}
