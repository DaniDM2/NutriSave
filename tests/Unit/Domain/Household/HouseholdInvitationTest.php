<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Household;

use App\Domain\Household\Exceptions\InvalidInvitationEmail;
use App\Domain\Household\Exceptions\InvalidInvitationTokenHash;
use App\Domain\Household\ValueObjects\HouseholdId;
use App\Domain\Household\ValueObjects\HouseholdInvitation;
use App\Domain\Household\ValueObjects\InvitationId;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class HouseholdInvitationTest extends TestCase
{
    public function test_it_normalizes_the_email(): void
    {
        $invitation = $this->invitation('  Ada@Example.COM ');

        $this->assertSame('ada@example.com', $invitation->email);
        $this->assertNull($invitation->id);
    }

    public function test_it_rejects_an_invalid_email(): void
    {
        $this->expectException(InvalidInvitationEmail::class);

        $this->invitation('not-an-email');
    }

    public function test_it_rejects_an_empty_token_hash(): void
    {
        $this->expectException(InvalidInvitationTokenHash::class);

        new HouseholdInvitation(
            new HouseholdId(1),
            'ada@example.com',
            new UserId(1),
            '',
            new DateTimeImmutable('+7 days'),
        );
    }

    public function test_it_assigns_a_persisted_id(): void
    {
        $invitation = $this->invitation('ada@example.com');

        $persisted = $invitation->withId(new InvitationId(4));

        $this->assertNull($invitation->id);
        $this->assertSame(4, $persisted->id?->value);
        $this->assertSame('ada@example.com', $persisted->email);
    }

    public function test_it_detects_expiry(): void
    {
        $expiresAt = new DateTimeImmutable('2026-01-08 12:00:00');
        $invitation = new HouseholdInvitation(
            new HouseholdId(1),
            'ada@example.com',
            new UserId(1),
            'hash',
            $expiresAt,
        );

        $this->assertFalse($invitation->isExpired(new DateTimeImmutable('2026-01-08 11:59:59')));
        $this->assertTrue($invitation->isExpired($expiresAt));
        $this->assertTrue($invitation->isExpired(new DateTimeImmutable('2026-01-08 12:00:01')));
    }

    public function test_it_hashes_a_plain_token(): void
    {
        $this->assertSame(hash('sha256', 'plain-token'), HouseholdInvitation::hashToken('plain-token'));
    }

    private function invitation(string $email): HouseholdInvitation
    {
        return new HouseholdInvitation(
            new HouseholdId(1),
            $email,
            new UserId(1),
            'hash',
            new DateTimeImmutable('+7 days'),
        );
    }
}
