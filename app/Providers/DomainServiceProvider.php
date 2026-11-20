<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Ai\Contracts\AiAgent;
use App\Domain\Household\Contracts\HouseholdInvitationNotifier;
use App\Domain\Household\Contracts\HouseholdInvitationRepository;
use App\Domain\Household\Contracts\HouseholdRepository;
use App\Domain\Pdf\Contracts\PdfGenerator;
use App\Domain\Shared\Contracts\UserDirectory;
use App\Infrastructure\Ai\NeuronAiAgent;
use App\Infrastructure\Mail\LaravelHouseholdInvitationNotifier;
use App\Infrastructure\Pdf\DomPdfGenerator;
use App\Infrastructure\Persistence\Eloquent\EloquentHouseholdInvitationRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentHouseholdRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentUserDirectory;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiAgent::class, NeuronAiAgent::class);
        $this->app->singleton(PdfGenerator::class, DomPdfGenerator::class);
        $this->app->singleton(HouseholdRepository::class, EloquentHouseholdRepository::class);
        $this->app->singleton(HouseholdInvitationRepository::class, EloquentHouseholdInvitationRepository::class);
        $this->app->singleton(UserDirectory::class, EloquentUserDirectory::class);
        $this->app->singleton(HouseholdInvitationNotifier::class, LaravelHouseholdInvitationNotifier::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/neuron'));
    }
}
