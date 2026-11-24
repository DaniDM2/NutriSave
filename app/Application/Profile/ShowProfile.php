<?php

declare(strict_types=1);

namespace App\Application\Profile;

use App\Domain\Profile\Contracts\ProfileRepository;
use App\Domain\Profile\ValueObjects\SportName;
use App\Domain\Shared\ValueObjects\UserId;

final readonly class ShowProfile
{
    public function __construct(
        private ProfileRepository $profiles,
    ) {}

    public function handle(UserId $userId): ?ProfileView
    {
        $profile = $this->profiles->findByUserId($userId);

        if ($profile === null || $profile->id === null) {
            return null;
        }

        return new ProfileView(
            $profile->id,
            $profile->sex->value,
            $profile->birthDate->value->format('Y-m-d'),
            $profile->height->centimeters,
            $profile->activityLevel->value,
            array_map(fn (SportName $sport): string => $sport->value, $profile->sports),
            $profile->goal->value,
            $profile->cadence->value,
        );
    }
}
