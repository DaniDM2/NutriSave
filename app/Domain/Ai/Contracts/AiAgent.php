<?php

declare(strict_types=1);

namespace App\Domain\Ai\Contracts;

use App\Domain\Ai\ValueObjects\AiMessage;

interface AiAgent
{
    public function chat(string $prompt): AiMessage;

    public function isConfigured(): bool;
}
