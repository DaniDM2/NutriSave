<?php

namespace Tests\Unit\Architecture;

use App\Domain\Ai\Contracts\AiAgent;
use App\Domain\Household\Contracts\HouseholdInvitationNotifier;
use App\Domain\Household\Contracts\HouseholdInvitationRepository;
use App\Domain\Household\Contracts\HouseholdRepository;
use App\Domain\Pdf\Contracts\PdfGenerator;
use App\Domain\Profile\Contracts\BodySnapshotRepository;
use App\Domain\Profile\Contracts\ProfileRepository;
use App\Domain\Shared\Contracts\UserDirectory;
use App\Infrastructure\Ai\NeuronAiAgent;
use App\Infrastructure\Mail\LaravelHouseholdInvitationNotifier;
use App\Infrastructure\Pdf\DomPdfGenerator;
use App\Infrastructure\Persistence\Eloquent\EloquentBodySnapshotRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentHouseholdInvitationRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentHouseholdRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentProfileRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentUserDirectory;
use Tests\TestCase;

class OnionBindingsTest extends TestCase
{
    public function test_domain_ports_are_bound_to_infrastructure_adapters(): void
    {
        $this->assertInstanceOf(NeuronAiAgent::class, $this->app->make(AiAgent::class));
        $this->assertInstanceOf(DomPdfGenerator::class, $this->app->make(PdfGenerator::class));
        $this->assertInstanceOf(EloquentHouseholdRepository::class, $this->app->make(HouseholdRepository::class));
        $this->assertInstanceOf(
            EloquentHouseholdInvitationRepository::class,
            $this->app->make(HouseholdInvitationRepository::class),
        );
        $this->assertInstanceOf(EloquentUserDirectory::class, $this->app->make(UserDirectory::class));
        $this->assertInstanceOf(
            LaravelHouseholdInvitationNotifier::class,
            $this->app->make(HouseholdInvitationNotifier::class),
        );
        $this->assertInstanceOf(EloquentProfileRepository::class, $this->app->make(ProfileRepository::class));
        $this->assertInstanceOf(
            EloquentBodySnapshotRepository::class,
            $this->app->make(BodySnapshotRepository::class),
        );
    }
}
