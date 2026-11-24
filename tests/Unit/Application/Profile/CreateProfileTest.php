<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Profile;

use App\Application\Profile\CreateProfile;
use App\Domain\Profile\ActivityLevel;
use App\Domain\Profile\Exceptions\DuplicateSport;
use App\Domain\Profile\Exceptions\InvalidBirthDate;
use App\Domain\Profile\Exceptions\InvalidHeight;
use App\Domain\Profile\Exceptions\InvalidSportName;
use App\Domain\Profile\Exceptions\UserAlreadyHasProfile;
use App\Domain\Profile\Goal;
use App\Domain\Profile\Sex;
use App\Domain\Profile\SnapshotCadence;
use App\Domain\Shared\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\InMemoryProfileRepository;

class CreateProfileTest extends TestCase
{
    public function test_it_creates_a_profile_for_a_user_without_one(): void
    {
        $profiles = new InMemoryProfileRepository;
        $userId = new UserId(1);

        $profile = (new CreateProfile($profiles))->handle(
            $userId,
            'male',
            '1996-11-21',
            180,
            'moderate',
            ['Running'],
            'maintain',
            7,
        );

        $this->assertNotNull($profile->id);
        $this->assertSame(1, $profile->userId->value);
        $this->assertSame(Sex::Male, $profile->sex);
        $this->assertSame('1996-11-21', $profile->birthDate->value->format('Y-m-d'));
        $this->assertSame(180, $profile->height->centimeters);
        $this->assertSame(ActivityLevel::Moderate, $profile->activityLevel);
        $this->assertSame(['Running'], array_map(
            fn ($sport): string => $sport->value,
            $profile->sports,
        ));
        $this->assertSame(Goal::Maintain, $profile->goal);
        $this->assertSame(SnapshotCadence::Weekly, $profile->cadence);
        $this->assertSame($profile, $profiles->findByUserId($userId));
    }

    public function test_it_rejects_creating_a_second_profile_for_the_same_user(): void
    {
        $profiles = new InMemoryProfileRepository;
        $create = new CreateProfile($profiles);
        $userId = new UserId(1);

        $create->handle($userId, 'male', '1996-11-21', 180, 'moderate', [], 'maintain', 7);

        $this->expectException(UserAlreadyHasProfile::class);

        $create->handle($userId, 'female', '1990-01-01', 165, 'light', [], 'lose', 14);
    }

    public function test_it_rejects_an_invalid_height(): void
    {
        $this->expectException(InvalidHeight::class);

        (new CreateProfile(new InMemoryProfileRepository))->handle(
            new UserId(1),
            'male',
            '1996-11-21',
            40,
            'moderate',
            [],
            'maintain',
            7,
        );
    }

    public function test_it_rejects_an_invalid_birth_date(): void
    {
        $this->expectException(InvalidBirthDate::class);

        (new CreateProfile(new InMemoryProfileRepository))->handle(
            new UserId(1),
            'male',
            '1899-12-31',
            180,
            'moderate',
            [],
            'maintain',
            7,
        );
    }

    public function test_it_rejects_an_invalid_sport_name(): void
    {
        $this->expectException(InvalidSportName::class);

        (new CreateProfile(new InMemoryProfileRepository))->handle(
            new UserId(1),
            'male',
            '1996-11-21',
            180,
            'moderate',
            ['   '],
            'maintain',
            7,
        );
    }

    public function test_it_rejects_duplicate_sports(): void
    {
        $this->expectException(DuplicateSport::class);

        (new CreateProfile(new InMemoryProfileRepository))->handle(
            new UserId(1),
            'male',
            '1996-11-21',
            180,
            'moderate',
            ['Running', 'running'],
            'maintain',
            7,
        );
    }
}
