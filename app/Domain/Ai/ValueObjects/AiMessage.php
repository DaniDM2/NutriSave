<?php

declare(strict_types=1);

namespace App\Domain\Ai\ValueObjects;

final readonly class AiMessage
{
    public function __construct(
        public string $content,
    ) {}
}
