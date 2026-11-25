<?php

declare(strict_types=1);

namespace App\Application\Profile;

use App\Domain\Profile\Contracts\BodySnapshotRepository;
use App\Domain\Profile\Contracts\ProfileRepository;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;

final readonly class ShowSnapshotStatus
{
    public function __construct(
        private ProfileRepository $profiles,
        private BodySnapshotRepository $snapshots,
    ) {}

    public function handle(UserId $userId, ?DateTimeImmutable $now = null): SnapshotStatusView
    {
        $now ??= new DateTimeImmutable;
        $profile = $this->profiles->findByUserId($userId);

        if ($profile === null || $profile->id === null) {
            return new SnapshotStatusView(false, false, false, null, null);
        }

        $snapshot = $this->snapshots->findLatestByUserId($userId);

        if ($snapshot === null) {
            return new SnapshotStatusView(true, false, false, $profile->cadence->value, null);
        }

        return new SnapshotStatusView(
            true,
            true,
            $snapshot->isStale($profile->cadence, $now),
            $profile->cadence->value,
            $snapshot->recordedAt->diff($now)->days,
        );
    }
}
