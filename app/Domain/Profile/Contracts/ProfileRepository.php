<?php

declare(strict_types=1);

namespace App\Domain\Profile\Contracts;

use App\Domain\Profile\ValueObjects\Profile;
use App\Domain\Profile\ValueObjects\ProfileId;
use App\Domain\Shared\ValueObjects\UserId;

interface ProfileRepository
{
    public function save(Profile $profile): Profile;

    public function findById(ProfileId $id): ?Profile;

    public function findByUserId(UserId $userId): ?Profile;
}
