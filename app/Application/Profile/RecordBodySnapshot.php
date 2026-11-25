<?php

declare(strict_types=1);

namespace App\Application\Profile;

use App\Domain\Profile\Contracts\BodySnapshotRepository;
use App\Domain\Profile\Contracts\ProfileRepository;
use App\Domain\Profile\Exceptions\ProfileNotFound;
use App\Domain\Profile\SnapshotOrigin;
use App\Domain\Profile\ValueObjects\BodyMassIndex;
use App\Domain\Profile\ValueObjects\BodySnapshot;
use App\Domain\Profile\ValueObjects\CompositionPercent;
use App\Domain\Profile\ValueObjects\Weight;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;

final readonly class RecordBodySnapshot
{
    public function __construct(
        private ProfileRepository $profiles,
        private BodySnapshotRepository $snapshots,
    ) {}

    public function handle(
        UserId $userId,
        float $weightKg,
        ?float $bodyFat = null,
        ?float $muscle = null,
        ?float $water = null,
    ): BodySnapshot {
        $profile = $this->profiles->findByUserId($userId);

        if ($profile === null || $profile->id === null) {
            throw new ProfileNotFound;
        }

        $weight = new Weight($weightKg);

        return $this->snapshots->save(BodySnapshot::create(
            $userId,
            $weight,
            BodyMassIndex::from($profile->height, $weight),
            SnapshotOrigin::Manual,
            new DateTimeImmutable,
            $bodyFat === null ? null : new CompositionPercent($bodyFat),
            $muscle === null ? null : new CompositionPercent($muscle),
            $water === null ? null : new CompositionPercent($water),
        ));
    }
}
