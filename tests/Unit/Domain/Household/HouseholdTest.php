<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Household;

use App\Domain\Household\Exceptions\DuplicateHouseholdMember;
use App\Domain\Household\Exceptions\HouseholdMustHaveExactlyOneOwner;
use App\Domain\Household\MemberRole;
use App\Domain\Household\ValueObjects\Household;
use App\Domain\Household\ValueObjects\HouseholdId;
use App\Domain\Household\ValueObjects\HouseholdMember;
use App\Domain\Household\ValueObjects\HouseholdName;
use App\Domain\Shared\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class HouseholdTest extends TestCase
{
    public function test_it_creates_a_household_without_a_persisted_id(): void
    {
        $name = new HouseholdName('Casa Ada');
        $ownerId = new UserId(1);

        $household = Household::create($name, $ownerId);

        $this->assertSame('Casa Ada', $household->name->value);
        $this->assertNull($household->id);
        $this->assertTrue($household->isOwner($ownerId));
        $this->assertTrue($household->hasMember($ownerId));
        $this->assertSame(MemberRole::Owner, $household->owner()->role);
        $this->assertSame(1, $household->owner()->userId->value);
        $this->assertCount(1, $household->members);
    }

    public function test_it_assigns_a_persisted_id(): void
    {
        $household = Household::create(new HouseholdName('Casa Ada'), new UserId(1));

        $persisted = $household->withId(new HouseholdId(10));

        $this->assertNull($household->id);
        $this->assertSame(10, $persisted->id?->value);
        $this->assertTrue($persisted->isOwner(new UserId(1)));
    }

    public function test_it_rejects_a_household_without_an_owner(): void
    {
        $this->expectException(HouseholdMustHaveExactlyOneOwner::class);

        new Household(
            new HouseholdName('Casa Ada'),
            [new HouseholdMember(new UserId(1), MemberRole::Member)],
        );
    }

    public function test_it_rejects_a_household_with_two_owners(): void
    {
        $this->expectException(HouseholdMustHaveExactlyOneOwner::class);

        new Household(
            new HouseholdName('Casa Ada'),
            [
                new HouseholdMember(new UserId(1), MemberRole::Owner),
                new HouseholdMember(new UserId(2), MemberRole::Owner),
            ],
        );
    }

    public function test_it_rejects_duplicate_members_in_the_constructor(): void
    {
        $this->expectException(DuplicateHouseholdMember::class);

        new Household(
            new HouseholdName('Casa Ada'),
            [
                new HouseholdMember(new UserId(1), MemberRole::Owner),
                new HouseholdMember(new UserId(1), MemberRole::Member),
            ],
        );
    }

    public function test_it_adds_a_member_without_mutating_the_original(): void
    {
        $household = Household::create(new HouseholdName('Casa Ada'), new UserId(1))
            ->withId(new HouseholdId(1));
        $newMemberId = new UserId(2);

        $updated = $household->addMember($newMemberId);

        $this->assertFalse($household->hasMember($newMemberId));
        $this->assertTrue($updated->hasMember($newMemberId));
        $this->assertFalse($updated->isOwner($newMemberId));
        $this->assertTrue($updated->isOwner(new UserId(1)));
        $this->assertCount(2, $updated->members);
        $this->assertSame(1, $updated->id?->value);
    }

    public function test_it_rejects_adding_a_duplicate_member(): void
    {
        $household = Household::create(new HouseholdName('Casa Ada'), new UserId(1));

        $this->expectException(DuplicateHouseholdMember::class);

        $household->addMember(new UserId(1));
    }
}
