<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Domain\Shared\Contracts\UserDirectory;
use App\Domain\Shared\ValueObjects\UserRecord;

final class InMemoryUserDirectory implements UserDirectory
{
    /** @var array<int, UserRecord> */
    private array $byId = [];

    public function add(UserRecord $record): void
    {
        $this->byId[$record->id->value] = $record;
    }

    public function findByEmail(string $email): ?UserRecord
    {
        $normalized = strtolower(trim($email));

        foreach ($this->byId as $record) {
            if (strtolower($record->email) === $normalized) {
                return $record;
            }
        }

        return null;
    }

    public function findByIds(array $ids): array
    {
        $records = [];

        foreach ($ids as $id) {
            if (isset($this->byId[$id->value])) {
                $records[$id->value] = $this->byId[$id->value];
            }
        }

        return $records;
    }
}
