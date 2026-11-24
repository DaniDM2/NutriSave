<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Profile;

use App\Application\Profile\CreateProfile;
use App\Application\Profile\UpdateProfile;
use App\Domain\Profile\ActivityLevel;
use App\Domain\Profile\Exceptions\InvalidHeight;
use App\Domain\Profile\Exceptions\ProfileNotFound;
use App\Domain\Profile\Goal;
use App\Domain\Profile\Sex;
use App\Domain\Profile\SnapshotCadence;
use App\Domain\Shared\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\InMemoryProfileRepository;

class UpdateProfileTest extends TestCase
{
    public function test_it_updates_an_existing_profile(): void
    {
        $profiles = new InMemoryProfileRepository;
        $userId = new UserId(1);
        $created = (new CreateProfile($profiles))->handle(
            $userId,
            'male',
            '1996-11-21',
            180,
            'moderate',
            ['Running'],
            'maintain',
            7,
        );

        $updated = (new UpdateProfile($profiles))->handle(
            $userId,
            'female',
            '1990-01-02',
            170,
            'light',
            ['Swimming'],
            'lose',
            30,
        );

        $this->assertSame($created->id?->value, $updated->id?->value);
        $this->assertSame(Sex::Female, $updated->sex);
        $this->assertSame('1990-01-02', $updated->birthDate->value->format('Y-m-d'));
        $this->assertSame(170, $updated->height->centimeters);
        $this->assertSame(ActivityLevel::Light, $updated->activityLevel);
        $this->assertSame(['Swimming'], array_map(
            fn ($sport): string => $sport->value,
            $updated->sports,
        ));
        $this->assertSame(Goal::Lose, $updated->goal);
        $this->assertSame(SnapshotCadence::Monthly, $updated->cadence);

        $found = $profiles->findByUserId($userId);
        $this->assertNotNull($found);
        $this->assertSame(170, $found->height->centimeters);
        $this->assertSame(Goal::Lose, $found->goal);
    }

    public function test_it_rejects_updating_when_the_user_has_no_profile(): void
    {
        $this->expectException(ProfileNotFound::class);

        (new UpdateProfile(new InMemoryProfileRepository))->handle(
            new UserId(1),
            'male',
            '1996-11-21',
            180,
            'moderate',
            [],
            'maintain',
            7,
        );
    }

    public function test_it_rejects_an_invalid_height(): void
    {
        $profiles = new InMemoryProfileRepository;
        $userId = new UserId(1);
        (new CreateProfile($profiles))->handle(
            $userId,
            'male',
            '1996-11-21',
            180,
            'moderate',
            [],
            'maintain',
            7,
        );

        $this->expectException(InvalidHeight::class);

        (new UpdateProfile($profiles))->handle(
            $userId,
            'male',
            '1996-11-21',
            300,
            'moderate',
            [],
            'maintain',
            7,
        );
    }
}
