<?php

declare(strict_types=1);

namespace App\Application\Ai;

use App\Domain\Ai\Contracts\AiAgent;
use App\Domain\Ai\ValueObjects\AiMessage;

class ChatWithAssistant
{
    public function __construct(
        private readonly AiAgent $agent,
    ) {}

    public function handle(string $prompt): AiMessage
    {
        return $this->agent->chat($prompt);
    }

    public function isConfigured(): bool
    {
        return $this->agent->isConfigured();
    }
}
