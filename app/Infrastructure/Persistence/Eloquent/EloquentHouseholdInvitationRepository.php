<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Household\Contracts\HouseholdInvitationRepository;
use App\Domain\Household\ValueObjects\HouseholdId;
use App\Domain\Household\ValueObjects\HouseholdInvitation;
use App\Domain\Household\ValueObjects\InvitationId;
use App\Domain\Shared\ValueObjects\UserId;
use App\Models\HouseholdInvitation as HouseholdInvitationModel;
use DateTimeImmutable;

final class EloquentHouseholdInvitationRepository implements HouseholdInvitationRepository
{
    public function save(HouseholdInvitation $invitation): HouseholdInvitation
    {
        $attributes = [
            'household_id' => $invitation->householdId->value,
            'email' => $invitation->email,
            'token_hash' => $invitation->tokenHash,
            'invited_by' => $invitation->invitedBy->value,
            'expires_at' => $invitation->expiresAt,
        ];

        if ($invitation->id === null) {
            $model = HouseholdInvitationModel::query()->create($attributes);

            return $invitation->withId(new InvitationId((int) $model->id));
        }

        $model = HouseholdInvitationModel::query()->findOrFail($invitation->id->value);
        $model->update($attributes);

        return $invitation;
    }

    public function findByTokenHash(string $tokenHash): ?HouseholdInvitation
    {
        $model = HouseholdInvitationModel::query()
            ->where('token_hash', $tokenHash)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function findPendingByHousehold(HouseholdId $householdId): array
    {
        $models = HouseholdInvitationModel::query()
            ->where('household_id', $householdId->value)
            ->orderBy('id')
            ->get();

        $invitations = [];

        foreach ($models as $model) {
            $invitations[] = $this->toDomain($model);
        }

        return $invitations;
    }

    public function findPendingByHouseholdAndEmail(HouseholdId $householdId, string $email): ?HouseholdInvitation
    {
        $model = HouseholdInvitationModel::query()
            ->where('household_id', $householdId->value)
            ->where('email', strtolower(trim($email)))
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function delete(HouseholdInvitation $invitation): void
    {
        if ($invitation->id === null) {
            return;
        }

        HouseholdInvitationModel::query()->whereKey($invitation->id->value)->delete();
    }

    private function toDomain(HouseholdInvitationModel $model): HouseholdInvitation
    {
        $expiresAt = $model->expires_at;
        $expiresAtImmutable = $expiresAt instanceof DateTimeImmutable
            ? $expiresAt
            : DateTimeImmutable::createFromInterface($expiresAt);

        return new HouseholdInvitation(
            new HouseholdId((int) $model->household_id),
            $model->email,
            new UserId((int) $model->invited_by),
            $model->token_hash,
            $expiresAtImmutable,
            new InvitationId((int) $model->id),
        );
    }
}
