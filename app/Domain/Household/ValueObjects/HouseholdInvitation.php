<?php

declare(strict_types=1);

namespace App\Domain\Household\ValueObjects;

use App\Domain\Household\Exceptions\InvalidInvitationEmail;
use App\Domain\Household\Exceptions\InvalidInvitationTokenHash;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;

final readonly class HouseholdInvitation
{
    public string $email;

    public function __construct(
        public HouseholdId $householdId,
        string $email,
        public UserId $invitedBy,
        public string $tokenHash,
        public DateTimeImmutable $expiresAt,
        public ?InvitationId $id = null,
    ) {
        $normalized = strtolower(trim($email));

        if ($normalized === '' || filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidInvitationEmail;
        }

        if ($tokenHash === '') {
            throw new InvalidInvitationTokenHash;
        }

        $this->email = $normalized;
    }

    public function withId(InvitationId $id): self
    {
        return new self(
            $this->householdId,
            $this->email,
            $this->invitedBy,
            $this->tokenHash,
            $this->expiresAt,
            $id,
        );
    }

    public function isExpired(?DateTimeImmutable $now = null): bool
    {
        return ($now ?? new DateTimeImmutable) >= $this->expiresAt;
    }

    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
