<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Profile\Contracts\BodySnapshotRepository;
use App\Domain\Profile\SnapshotOrigin;
use App\Domain\Profile\ValueObjects\BodyMassIndex;
use App\Domain\Profile\ValueObjects\BodySnapshot;
use App\Domain\Profile\ValueObjects\BodySnapshotId;
use App\Domain\Profile\ValueObjects\CompositionPercent;
use App\Domain\Profile\ValueObjects\Weight;
use App\Domain\Shared\ValueObjects\UserId;
use App\Models\BodySnapshot as BodySnapshotModel;
use DateTimeImmutable;

final class EloquentBodySnapshotRepository implements BodySnapshotRepository
{
    public function save(BodySnapshot $snapshot): BodySnapshot
    {
        $attributes = [
            'user_id' => $snapshot->userId->value,
            'weight_kg' => $snapshot->weight->kilograms,
            'bmi' => $snapshot->bmi->value,
            'body_fat_percent' => $snapshot->bodyFat?->value,
            'muscle_percent' => $snapshot->muscle?->value,
            'water_percent' => $snapshot->water?->value,
            'origin' => $snapshot->origin->value,
            'photo_paths' => $snapshot->photoPaths === [] ? null : $snapshot->photoPaths,
            'recorded_at' => $snapshot->recordedAt,
        ];

        if ($snapshot->id === null) {
            $model = BodySnapshotModel::query()->create($attributes);

            return $snapshot->withId(new BodySnapshotId((int) $model->id));
        }

        $model = BodySnapshotModel::query()->findOrFail($snapshot->id->value);
        $model->update($attributes);

        return $snapshot;
    }

    public function findById(BodySnapshotId $id): ?BodySnapshot
    {
        $model = BodySnapshotModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findLatestByUserId(UserId $userId): ?BodySnapshot
    {
        $model = BodySnapshotModel::query()
            ->where('user_id', $userId->value)
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function listByUserId(UserId $userId): array
    {
        $models = BodySnapshotModel::query()
            ->where('user_id', $userId->value)
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->get();

        $snapshots = [];

        foreach ($models as $model) {
            $snapshots[] = $this->toDomain($model);
        }

        return $snapshots;
    }

    private function toDomain(BodySnapshotModel $model): BodySnapshot
    {
        $recordedAt = $model->recorded_at;
        $recordedAtImmutable = $recordedAt instanceof DateTimeImmutable
            ? $recordedAt
            : DateTimeImmutable::createFromInterface($recordedAt);

        return new BodySnapshot(
            new UserId((int) $model->user_id),
            new Weight((float) $model->weight_kg),
            BodyMassIndex::of((float) $model->bmi),
            SnapshotOrigin::from($model->origin),
            $recordedAtImmutable,
            $this->percent($model->body_fat_percent),
            $this->percent($model->muscle_percent),
            $this->percent($model->water_percent),
            array_values($model->photo_paths ?? []),
            new BodySnapshotId((int) $model->id),
        );
    }

    private function percent(mixed $value): ?CompositionPercent
    {
        if ($value === null) {
            return null;
        }

        return new CompositionPercent((float) $value);
    }
}
