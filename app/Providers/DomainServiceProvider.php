<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Ai\Contracts\AiAgent;
use App\Domain\Household\Contracts\HouseholdRepository;
use App\Domain\Pdf\Contracts\PdfGenerator;
use App\Infrastructure\Ai\NeuronAiAgent;
use App\Infrastructure\Pdf\DomPdfGenerator;
use App\Infrastructure\Persistence\Eloquent\EloquentHouseholdRepository;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiAgent::class, NeuronAiAgent::class);
        $this->app->singleton(PdfGenerator::class, DomPdfGenerator::class);
        $this->app->singleton(HouseholdRepository::class, EloquentHouseholdRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/neuron'));
    }
}
