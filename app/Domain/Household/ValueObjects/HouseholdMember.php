<?php

declare(strict_types=1);

namespace App\Domain\Household\ValueObjects;

use App\Domain\Household\MemberRole;
use App\Domain\Shared\ValueObjects\UserId;

final readonly class HouseholdMember
{
    public function __construct(
        public UserId $userId,
        public MemberRole $role,
    ) {}
}
