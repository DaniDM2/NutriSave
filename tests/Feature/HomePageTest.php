<?php

namespace Tests\Feature;

use App\Infrastructure\Queue\GenerateSamplePdfJob;
use App\Livewire\Home;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_the_home_page_renders_the_stack(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('NutriSave')
            ->assertSee('Livewire 4')
            ->assertSee('Neuron AI');
    }

    public function test_pdf_job_can_be_queued_from_the_home_page(): void
    {
        Queue::fake();

        Livewire::test(Home::class)
            ->call('enqueuePdf')
            ->assertSet('status', 'El PDF se encoló en Horizon. Cuando termine, estará en storage/app/private/pdf/.');

        Queue::assertPushed(GenerateSamplePdfJob::class);
    }
}
