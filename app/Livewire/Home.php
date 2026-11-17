<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Application\Ai\ChatWithAssistant;
use App\Domain\Ai\Exceptions\AiProviderNotConfigured;
use App\Infrastructure\Queue\GenerateSamplePdfJob;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('NutriSave')]
class Home extends Component
{
    public string $prompt = '';

    public string $reply = '';

    public string $status = '';

    public function enqueuePdf(): void
    {
        GenerateSamplePdfJob::dispatch();

        $this->status = 'El PDF se encoló en Horizon. Cuando termine, estará en storage/app/private/pdf/.';
    }

    public function askAssistant(ChatWithAssistant $chat): void
    {
        $this->validate([
            'prompt' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $this->reply = $chat->handle($this->prompt)->content;
            $this->status = '';
        } catch (AiProviderNotConfigured $exception) {
            $this->reply = '';
            $this->status = $exception->getMessage();
        }
    }

    public function render(ChatWithAssistant $chat)
    {
        return view('livewire.home', [
            'aiConfigured' => $chat->isConfigured(),
        ]);
    }
}
