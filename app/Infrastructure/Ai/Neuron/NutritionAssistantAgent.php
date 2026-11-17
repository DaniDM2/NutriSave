<?php

declare(strict_types=1);

namespace App\Infrastructure\Ai\Neuron;

use NeuronAI\Agent\Agent;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Laravel\Facades\AIProvider;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\Toolkits\ToolkitInterface;

class NutritionAssistantAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return AIProvider::driver();
    }

    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: config('neuron.system_prompt')['background'],
            steps: config('neuron.system_prompt')['steps'],
            output: config('neuron.system_prompt')['output'],
        );
    }

    /**
     * @return ToolInterface[]|ToolkitInterface[]
     */
    protected function tools(): array
    {
        return [];
    }
}
