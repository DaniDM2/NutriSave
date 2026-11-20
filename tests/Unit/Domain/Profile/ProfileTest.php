<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Profile;

use App\Domain\Profile\ActivityLevel;
use App\Domain\Profile\Exceptions\DuplicateSport;
use App\Domain\Profile\Exceptions\InvalidBirthDate;
use App\Domain\Profile\Goal;
use App\Domain\Profile\Sex;
use App\Domain\Profile\SnapshotCadence;
use App\Domain\Profile\ValueObjects\BirthDate;
use App\Domain\Profile\ValueObjects\Height;
use App\Domain\Profile\ValueObjects\Profile;
use App\Domain\Profile\ValueObjects\ProfileId;
use App\Domain\Profile\ValueObjects\SportName;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    public function test_it_creates_a_profile_without_a_persisted_id(): void
    {
        $profile = $this->profile();

        $this->assertNull($profile->id);
        $this->assertSame(1, $profile->userId->value);
        $this->assertSame(Sex::Male, $profile->sex);
        $this->assertSame(180, $profile->height->centimeters);
        $this->assertSame(ActivityLevel::Moderate, $profile->activityLevel);
        $this->assertSame(Goal::Maintain, $profile->goal);
        $this->assertSame(SnapshotCadence::Weekly, $profile->cadence);
        $this->assertCount(0, $profile->sports);
    }

    public function test_it_assigns_a_persisted_id(): void
    {
        $profile = $this->profile();

        $persisted = $profile->withId(new ProfileId(10));

        $this->assertNull($profile->id);
        $this->assertSame(10, $persisted->id?->value);
        $this->assertSame(1, $persisted->userId->value);
    }

    public function test_it_accepts_sports(): void
    {
        $profile = $this->profile(sports: [new SportName('Running'), new SportName('Swimming')]);

        $this->assertSame(['Running', 'Swimming'], array_map(
            fn (SportName $sport): string => $sport->value,
            $profile->sports,
        ));
    }

    public function test_it_rejects_duplicate_sports_case_insensitively(): void
    {
        $this->expectException(DuplicateSport::class);

        $this->profile(sports: [new SportName('Running'), new SportName('running')]);
    }

    public function test_it_computes_age_in_full_years(): void
    {
        $profile = $this->profile();

        $this->assertSame(30, $profile->ageOn(new DateTimeImmutable('2026-11-21')));
        $this->assertSame(29, $profile->ageOn(new DateTimeImmutable('2026-11-20')));
    }

    public function test_it_rejects_age_on_the_birth_date(): void
    {
        $this->expectException(InvalidBirthDate::class);

        $this->profile()->ageOn(new DateTimeImmutable('1996-11-21'));
    }

    public function test_it_rejects_age_before_the_birth_date(): void
    {
        $this->expectException(InvalidBirthDate::class);

        $this->profile()->ageOn(new DateTimeImmutable('1996-11-20'));
    }

    /**
     * @param  list<SportName>  $sports
     */
    private function profile(array $sports = []): Profile
    {
        return Profile::create(
            new UserId(1),
            Sex::Male,
            new BirthDate(new DateTimeImmutable('1996-11-21')),
            new Height(180),
            ActivityLevel::Moderate,
            $sports,
            Goal::Maintain,
            SnapshotCadence::Weekly,
        );
    }
}
