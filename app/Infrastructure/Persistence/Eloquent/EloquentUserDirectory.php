<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Shared\Contracts\UserDirectory;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\ValueObjects\UserRecord;
use App\Models\User;

final class EloquentUserDirectory implements UserDirectory
{
    public function findByEmail(string $email): ?UserRecord
    {
        $user = User::query()
            ->whereRaw('lower(email) = ?', [strtolower(trim($email))])
            ->first();

        return $user === null ? null : $this->toRecord($user);
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $values = array_map(fn (UserId $id): int => $id->value, $ids);
        $records = [];

        foreach (User::query()->whereIn('id', $values)->get() as $user) {
            $records[(int) $user->id] = $this->toRecord($user);
        }

        return $records;
    }

    private function toRecord(User $user): UserRecord
    {
        return new UserRecord(
            new UserId((int) $user->id),
            $user->name,
            $user->email,
        );
    }
}
