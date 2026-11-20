<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Household;

use App\Application\Household\CreateHousehold;
use App\Domain\Household\Exceptions\InvalidHouseholdName;
use App\Domain\Household\Exceptions\UserAlreadyInHousehold;
use App\Domain\Shared\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\InMemoryHouseholdRepository;

class CreateHouseholdTest extends TestCase
{
    public function test_it_creates_a_household_for_a_user_without_one(): void
    {
        $households = new InMemoryHouseholdRepository;
        $ownerId = new UserId(1);

        $household = (new CreateHousehold($households))->handle($ownerId, 'Casa Ada');

        $this->assertSame('Casa Ada', $household->name->value);
        $this->assertNotNull($household->id);
        $this->assertTrue($household->isOwner($ownerId));
        $this->assertSame($household, $households->findByUserId($ownerId));
    }

    public function test_it_rejects_creating_a_second_household_for_the_same_user(): void
    {
        $households = new InMemoryHouseholdRepository;
        $ownerId = new UserId(1);
        $create = new CreateHousehold($households);
        $create->handle($ownerId, 'Casa Ada');

        $this->expectException(UserAlreadyInHousehold::class);

        $create->handle($ownerId, 'Casa Bob');
    }

    public function test_it_rejects_an_invalid_household_name(): void
    {
        $this->expectException(InvalidHouseholdName::class);

        (new CreateHousehold(new InMemoryHouseholdRepository))->handle(new UserId(1), '   ');
    }
}
