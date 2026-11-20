<?php

declare(strict_types=1);

namespace App\Application\Household;

use App\Domain\Household\MemberRole;
use App\Domain\Shared\ValueObjects\UserId;

final readonly class HouseholdMemberView
{
    public function __construct(
        public UserId $userId,
        public string $name,
        public string $email,
        public MemberRole $role,
    ) {}
}
