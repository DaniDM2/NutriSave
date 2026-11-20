<?php

declare(strict_types=1);

namespace App\Domain\Shared\Contracts;

use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\ValueObjects\UserRecord;

interface UserDirectory
{
    public function findByEmail(string $email): ?UserRecord;

    /**
     * @param  list<UserId>  $ids
     * @return array<int, UserRecord>
     */
    public function findByIds(array $ids): array;
}
