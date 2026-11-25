<?php

declare(strict_types=1);

namespace App\Application\Profile;

use App\Domain\Profile\Contracts\BodySnapshotRepository;
use App\Domain\Shared\ValueObjects\UserId;

final readonly class ListBodySnapshots
{
    public function __construct(
        private BodySnapshotRepository $snapshots,
    ) {}

    /**
     * @return list<BodySnapshotView>
     */
    public function handle(UserId $userId): array
    {
        $views = [];

        foreach ($this->snapshots->listByUserId($userId) as $snapshot) {
            $view = BodySnapshotView::fromSnapshot($snapshot);

            if ($view === null) {
                continue;
            }

            $views[] = $view;
        }

        return $views;
    }
}
