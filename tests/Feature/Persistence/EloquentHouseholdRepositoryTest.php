<?php

declare(strict_types=1);

namespace Tests\Feature\Persistence;

use App\Domain\Household\Exceptions\UserAlreadyInHousehold;
use App\Domain\Household\MemberRole;
use App\Domain\Household\ValueObjects\Household;
use App\Domain\Household\ValueObjects\HouseholdId;
use App\Domain\Household\ValueObjects\HouseholdName;
use App\Domain\Shared\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\EloquentHouseholdRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentHouseholdRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_a_new_household_and_returns_it_with_a_persisted_id(): void
    {
        $owner = User::factory()->create();
        $ownerId = new UserId((int) $owner->id);
        $household = Household::create(new HouseholdName('Casa Ada'), $ownerId);

        $persisted = $this->repository()->save($household);

        $this->assertNull($household->id);
        $this->assertNotNull($persisted->id);
        $this->assertGreaterThan(0, $persisted->id->value);
        $this->assertSame('Casa Ada', $persisted->name->value);
        $this->assertTrue($persisted->isOwner($ownerId));
        $this->assertDatabaseHas('households', [
            'id' => $persisted->id->value,
            'name' => 'Casa Ada',
        ]);
        $this->assertDatabaseHas('household_members', [
            'household_id' => $persisted->id->value,
            'user_id' => $owner->id,
            'role' => MemberRole::Owner->value,
        ]);
    }

    public function test_it_finds_a_household_by_id(): void
    {
        $owner = User::factory()->create();
        $saved = $this->repository()->save(
            Household::create(new HouseholdName('Casa Ada'), new UserId((int) $owner->id)),
        );

        $this->assertNotNull($saved->id);
        $found = $this->repository()->findById($saved->id);

        $this->assertNotNull($found);
        $this->assertSame($saved->id->value, $found->id?->value);
        $this->assertSame('Casa Ada', $found->name->value);
        $this->assertTrue($found->isOwner(new UserId((int) $owner->id)));
    }

    public function test_it_returns_null_when_finding_an_unknown_household_id(): void
    {
        $found = $this->repository()->findById(new HouseholdId(999));

        $this->assertNull($found);
    }

    public function test_it_finds_a_household_by_owner_user_id(): void
    {
        $owner = User::factory()->create();
        $saved = $this->repository()->save(
            Household::create(new HouseholdName('Casa Ada'), new UserId((int) $owner->id)),
        );

        $found = $this->repository()->findByUserId(new UserId((int) $owner->id));

        $this->assertNotNull($found);
        $this->assertSame($saved->id?->value, $found->id?->value);
    }

    public function test_it_returns_null_when_finding_by_an_unknown_user_id(): void
    {
        $user = User::factory()->create();

        $found = $this->repository()->findByUserId(new UserId((int) $user->id));

        $this->assertNull($found);
    }

    public function test_it_saves_an_added_member_and_finds_the_household_by_that_user(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $repository = $this->repository();

        $saved = $repository->save(
            Household::create(new HouseholdName('Casa Ada'), new UserId((int) $owner->id)),
        );
        $withMember = $repository->save($saved->addMember(new UserId((int) $member->id)));

        $this->assertFalse($saved->hasMember(new UserId((int) $member->id)));
        $this->assertTrue($withMember->hasMember(new UserId((int) $member->id)));
        $this->assertFalse($withMember->isOwner(new UserId((int) $member->id)));
        $this->assertCount(2, $withMember->members);
        $this->assertDatabaseCount('household_members', 2);

        $found = $repository->findByUserId(new UserId((int) $member->id));

        $this->assertNotNull($found);
        $this->assertSame($withMember->id?->value, $found->id?->value);
        $this->assertTrue($found->hasMember(new UserId((int) $owner->id)));
        $this->assertTrue($found->hasMember(new UserId((int) $member->id)));
    }

    public function test_it_rejects_saving_a_second_household_for_the_same_user(): void
    {
        $owner = User::factory()->create();
        $ownerId = new UserId((int) $owner->id);
        $this->repository()->save(Household::create(new HouseholdName('Casa Ada'), $ownerId));

        $this->expectException(UserAlreadyInHousehold::class);

        $this->repository()->save(Household::create(new HouseholdName('Casa Bob'), $ownerId));
    }

    private function repository(): EloquentHouseholdRepository
    {
        return new EloquentHouseholdRepository;
    }
}
