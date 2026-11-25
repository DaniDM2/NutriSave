<?php

declare(strict_types=1);

namespace App\Application\Profile;

final readonly class SnapshotStatusView
{
    public function __construct(
        public bool $hasProfile,
        public bool $hasSnapshot,
        public bool $isStale,
        public ?int $cadenceDays,
        public ?int $daysSinceRecorded,
    ) {}
}
