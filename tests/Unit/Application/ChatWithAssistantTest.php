<?php

namespace Tests\Unit\Application;

use App\Application\Ai\ChatWithAssistant;
use App\Domain\Ai\Contracts\AiAgent;
use App\Domain\Ai\ValueObjects\AiMessage;
use Tests\TestCase;

class ChatWithAssistantTest extends TestCase
{
    public function test_it_returns_the_agent_message(): void
    {
        $agent = new class implements AiAgent
        {
            public function chat(string $prompt): AiMessage
            {
                return new AiMessage('Respuesta a: '.$prompt);
            }

            public function isConfigured(): bool
            {
                return true;
            }
        };

        $useCase = new ChatWithAssistant($agent);

        $this->assertTrue($useCase->isConfigured());
        $this->assertSame('Respuesta a: hola', $useCase->handle('hola')->content);
    }
}
