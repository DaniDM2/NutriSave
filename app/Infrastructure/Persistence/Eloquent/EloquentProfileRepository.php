<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Profile\ActivityLevel;
use App\Domain\Profile\Contracts\ProfileRepository;
use App\Domain\Profile\Exceptions\UserAlreadyHasProfile;
use App\Domain\Profile\Goal;
use App\Domain\Profile\Sex;
use App\Domain\Profile\SnapshotCadence;
use App\Domain\Profile\ValueObjects\BirthDate;
use App\Domain\Profile\ValueObjects\Height;
use App\Domain\Profile\ValueObjects\Profile;
use App\Domain\Profile\ValueObjects\ProfileId;
use App\Domain\Profile\ValueObjects\SportName;
use App\Domain\Shared\ValueObjects\UserId;
use App\Models\Profile as ProfileModel;
use DateTimeImmutable;
use Illuminate\Database\UniqueConstraintViolationException;

final class EloquentProfileRepository implements ProfileRepository
{
    public function save(Profile $profile): Profile
    {
        try {
            return $this->persist($profile);
        } catch (UniqueConstraintViolationException) {
            throw new UserAlreadyHasProfile;
        }
    }

    public function findById(ProfileId $id): ?Profile
    {
        $model = ProfileModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByUserId(UserId $userId): ?Profile
    {
        $model = ProfileModel::query()
            ->where('user_id', $userId->value)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    private function persist(Profile $profile): Profile
    {
        $attributes = [
            'user_id' => $profile->userId->value,
            'sex' => $profile->sex->value,
            'birth_date' => $profile->birthDate->value->format('Y-m-d'),
            'height_cm' => $profile->height->centimeters,
            'activity_level' => $profile->activityLevel->value,
            'sports' => array_map(
                fn (SportName $sport): string => $sport->value,
                $profile->sports,
            ),
            'goal' => $profile->goal->value,
            'cadence' => $profile->cadence->value,
        ];

        if ($profile->id === null) {
            $model = ProfileModel::query()->create($attributes);

            return $profile->withId(new ProfileId((int) $model->id));
        }

        $model = ProfileModel::query()->findOrFail($profile->id->value);
        $model->update($attributes);

        return $profile;
    }

    private function toDomain(ProfileModel $model): Profile
    {
        $birthDate = $model->birth_date;
        $birthDateImmutable = $birthDate instanceof DateTimeImmutable
            ? $birthDate
            : DateTimeImmutable::createFromInterface($birthDate);

        $sports = [];

        foreach ($model->sports ?? [] as $sport) {
            $sports[] = new SportName($sport);
        }

        return new Profile(
            new UserId((int) $model->user_id),
            Sex::from($model->sex),
            new BirthDate($birthDateImmutable),
            new Height((int) $model->height_cm),
            ActivityLevel::from($model->activity_level),
            $sports,
            Goal::from($model->goal),
            SnapshotCadence::from((int) $model->cadence),
            new ProfileId((int) $model->id),
        );
    }
}
