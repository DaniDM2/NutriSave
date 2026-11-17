<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Ai\Contracts\AiAgent;
use App\Domain\Pdf\Contracts\PdfGenerator;
use App\Infrastructure\Ai\NeuronAiAgent;
use App\Infrastructure\Pdf\DomPdfGenerator;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiAgent::class, NeuronAiAgent::class);
        $this->app->singleton(PdfGenerator::class, DomPdfGenerator::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/neuron'));
    }
}
