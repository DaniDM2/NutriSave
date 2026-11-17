<?php

declare(strict_types=1);

namespace App\Infrastructure\Ai;

use App\Domain\Ai\Contracts\AiAgent;
use App\Domain\Ai\Exceptions\AiProviderNotConfigured;
use App\Domain\Ai\ValueObjects\AiMessage;
use App\Infrastructure\Ai\Neuron\NutritionAssistantAgent;
use NeuronAI\Chat\Messages\UserMessage;

final class NeuronAiAgent implements AiAgent
{
    public function __construct(
        private readonly NutritionAssistantAgent $agent,
    ) {}

    public function isConfigured(): bool
    {
        $provider = config('neuron.provider.default');

        return is_string($provider) && $provider !== '';
    }

    public function chat(string $prompt): AiMessage
    {
        if (! $this->isConfigured()) {
            throw new AiProviderNotConfigured(
                'Configura NEURON_AI_PROVIDER y la API key del proveedor en el archivo .env.'
            );
        }

        $state = $this->agent->chat(new UserMessage($prompt));
        $content = $state->getMessage()?->getContent() ?? '';

        return new AiMessage($content);
    }
}
