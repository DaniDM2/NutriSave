<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Household\Contracts\HouseholdRepository;
use App\Domain\Household\MemberRole;
use App\Domain\Household\ValueObjects\Household;
use App\Domain\Household\ValueObjects\HouseholdId;
use App\Domain\Household\ValueObjects\HouseholdMember;
use App\Domain\Household\ValueObjects\HouseholdName;
use App\Domain\Shared\ValueObjects\UserId;
use App\Models\Household as HouseholdModel;
use App\Models\HouseholdMember as HouseholdMemberModel;
use Illuminate\Support\Facades\DB;

final class EloquentHouseholdRepository implements HouseholdRepository
{
    public function save(Household $household): Household
    {
        return DB::transaction(function () use ($household): Household {
            if ($household->id === null) {
                $model = HouseholdModel::query()->create([
                    'name' => $household->name->value,
                ]);

                $household = $household->withId(new HouseholdId((int) $model->id));
            } else {
                $model = HouseholdModel::query()->findOrFail($household->id->value);
                $model->update(['name' => $household->name->value]);
                $model->members()->delete();
            }

            foreach ($household->members as $member) {
                $model->members()->create([
                    'user_id' => $member->userId->value,
                    'role' => $member->role->value,
                ]);
            }

            return $household;
        });
    }

    public function findById(HouseholdId $id): ?Household
    {
        $model = HouseholdModel::query()
            ->with('members')
            ->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByUserId(UserId $userId): ?Household
    {
        $member = HouseholdMemberModel::query()
            ->where('user_id', $userId->value)
            ->first();

        if ($member === null) {
            return null;
        }

        return $this->findById(new HouseholdId((int) $member->household_id));
    }

    private function toDomain(HouseholdModel $model): Household
    {
        $members = [];

        foreach ($model->members as $member) {
            $members[] = new HouseholdMember(
                new UserId((int) $member->user_id),
                MemberRole::from($member->role),
            );
        }

        return new Household(
            new HouseholdName($model->name),
            $members,
            new HouseholdId((int) $model->id),
        );
    }
}
