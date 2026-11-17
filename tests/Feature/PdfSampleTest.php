<?php

namespace Tests\Feature;

use App\Application\Pdf\GenerateDocument;
use App\Domain\Pdf\ValueObjects\PdfDocument;
use Tests\TestCase;

class PdfSampleTest extends TestCase
{
    public function test_sample_pdf_can_be_downloaded(): void
    {
        $this->mock(GenerateDocument::class, function ($mock): void {
            $mock->shouldReceive('sample')
                ->once()
                ->andReturn(new PdfDocument('nutrisave-muestra.pdf', '%PDF-1.4 test'));
        });

        $this->get(route('pdf.sample'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="nutrisave-muestra.pdf"');
    }
}
