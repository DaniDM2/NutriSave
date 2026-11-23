<?php

declare(strict_types=1);

namespace App\Domain\Profile\Contracts;

use App\Domain\Profile\ValueObjects\BodySnapshot;
use App\Domain\Profile\ValueObjects\BodySnapshotId;
use App\Domain\Shared\ValueObjects\UserId;

interface BodySnapshotRepository
{
    public function save(BodySnapshot $snapshot): BodySnapshot;

    public function findById(BodySnapshotId $id): ?BodySnapshot;

    public function findLatestByUserId(UserId $userId): ?BodySnapshot;

    /**
     * @return list<BodySnapshot>
     */
    public function listByUserId(UserId $userId): array;
}
