<?php

declare(strict_types=1);

namespace App\Domain\Household\ValueObjects;

use App\Domain\Household\Exceptions\DuplicateHouseholdMember;
use App\Domain\Household\Exceptions\HouseholdMustHaveExactlyOneOwner;
use App\Domain\Household\MemberRole;
use App\Domain\Shared\ValueObjects\UserId;

final readonly class Household
{
    /**
     * @param  list<HouseholdMember>  $members
     */
    public function __construct(
        public HouseholdName $name,
        public array $members,
        public ?HouseholdId $id = null,
    ) {
        $this->assertUniqueMembers();
        $this->assertExactlyOneOwner();
    }

    public static function create(HouseholdName $name, UserId $ownerId): self
    {
        return new self(
            $name,
            [new HouseholdMember($ownerId, MemberRole::Owner)],
        );
    }

    public function withId(HouseholdId $id): self
    {
        return new self($this->name, $this->members, $id);
    }

    public function addMember(UserId $userId): self
    {
        if ($this->hasMember($userId)) {
            throw new DuplicateHouseholdMember;
        }

        return new self($this->name, [
            ...$this->members,
            new HouseholdMember($userId, MemberRole::Member),
        ], $this->id);
    }

    public function hasMember(UserId $userId): bool
    {
        foreach ($this->members as $member) {
            if ($member->userId->value === $userId->value) {
                return true;
            }
        }

        return false;
    }

    public function isOwner(UserId $userId): bool
    {
        return $this->owner()->userId->value === $userId->value;
    }

    public function owner(): HouseholdMember
    {
        foreach ($this->members as $member) {
            if ($member->role === MemberRole::Owner) {
                return $member;
            }
        }

        throw new HouseholdMustHaveExactlyOneOwner;
    }

    private function assertUniqueMembers(): void
    {
        $ids = array_map(
            fn (HouseholdMember $member): int => $member->userId->value,
            $this->members,
        );

        if (count($ids) !== count(array_unique($ids))) {
            throw new DuplicateHouseholdMember;
        }
    }

    private function assertExactlyOneOwner(): void
    {
        $owners = array_filter(
            $this->members,
            fn (HouseholdMember $member): bool => $member->role === MemberRole::Owner,
        );

        if (count($owners) !== 1) {
            throw new HouseholdMustHaveExactlyOneOwner;
        }
    }
}
