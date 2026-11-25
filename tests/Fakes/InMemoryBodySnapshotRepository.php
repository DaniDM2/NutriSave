<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Domain\Profile\Contracts\BodySnapshotRepository;
use App\Domain\Profile\ValueObjects\BodySnapshot;
use App\Domain\Profile\ValueObjects\BodySnapshotId;
use App\Domain\Shared\ValueObjects\UserId;

final class InMemoryBodySnapshotRepository implements BodySnapshotRepository
{
    /** @var array<int, BodySnapshot> */
    private array $snapshots = [];

    private int $nextId = 1;

    public function save(BodySnapshot $snapshot): BodySnapshot
    {
        if ($snapshot->id === null) {
            $snapshot = $snapshot->withId(new BodySnapshotId($this->nextId));
            $this->nextId++;
        }

        $this->snapshots[$snapshot->id->value] = $snapshot;

        return $snapshot;
    }

    public function findById(BodySnapshotId $id): ?BodySnapshot
    {
        return $this->snapshots[$id->value] ?? null;
    }

    public function findLatestByUserId(UserId $userId): ?BodySnapshot
    {
        $snapshots = $this->forUser($userId);

        return $snapshots[0] ?? null;
    }

    public function listByUserId(UserId $userId): array
    {
        return $this->forUser($userId);
    }

    /**
     * @return list<BodySnapshot>
     */
    private function forUser(UserId $userId): array
    {
        $snapshots = array_values(array_filter(
            $this->snapshots,
            fn (BodySnapshot $snapshot): bool => $snapshot->userId->value === $userId->value,
        ));

        usort(
            $snapshots,
            function (BodySnapshot $a, BodySnapshot $b): int {
                $recorded = $b->recordedAt <=> $a->recordedAt;

                if ($recorded !== 0) {
                    return $recorded;
                }

                return ($b->id?->value ?? 0) <=> ($a->id?->value ?? 0);
            },
        );

        return $snapshots;
    }
}
