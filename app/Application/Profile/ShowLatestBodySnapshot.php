<?php

declare(strict_types=1);

namespace App\Application\Profile;

use App\Domain\Profile\Contracts\BodySnapshotRepository;
use App\Domain\Shared\ValueObjects\UserId;

final readonly class ShowLatestBodySnapshot
{
    public function __construct(
        private BodySnapshotRepository $snapshots,
    ) {}

    public function handle(UserId $userId): ?BodySnapshotView
    {
        $snapshot = $this->snapshots->findLatestByUserId($userId);

        if ($snapshot === null) {
            return null;
        }

        return BodySnapshotView::fromSnapshot($snapshot);
    }
}
