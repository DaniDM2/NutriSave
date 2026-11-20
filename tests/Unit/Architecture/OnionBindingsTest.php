<?php

namespace Tests\Unit\Architecture;

use App\Domain\Ai\Contracts\AiAgent;
use App\Domain\Household\Contracts\HouseholdRepository;
use App\Domain\Pdf\Contracts\PdfGenerator;
use App\Infrastructure\Ai\NeuronAiAgent;
use App\Infrastructure\Pdf\DomPdfGenerator;
use App\Infrastructure\Persistence\Eloquent\EloquentHouseholdRepository;
use Tests\TestCase;

class OnionBindingsTest extends TestCase
{
    public function test_domain_ports_are_bound_to_infrastructure_adapters(): void
    {
        $this->assertInstanceOf(NeuronAiAgent::class, $this->app->make(AiAgent::class));
        $this->assertInstanceOf(DomPdfGenerator::class, $this->app->make(PdfGenerator::class));
        $this->assertInstanceOf(EloquentHouseholdRepository::class, $this->app->make(HouseholdRepository::class));
    }
}
