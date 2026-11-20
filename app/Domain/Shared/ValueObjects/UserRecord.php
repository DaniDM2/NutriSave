<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

final readonly class UserRecord
{
    public function __construct(
        public UserId $id,
        public string $name,
        public string $email,
    ) {}
}
