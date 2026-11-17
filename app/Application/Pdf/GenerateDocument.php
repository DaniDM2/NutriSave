<?php

declare(strict_types=1);

namespace App\Application\Pdf;

use App\Domain\Pdf\Contracts\PdfGenerator;
use App\Domain\Pdf\ValueObjects\PdfDocument;
use DateTimeImmutable;
use DateTimeInterface;

class GenerateDocument
{
    public function __construct(
        private readonly PdfGenerator $pdfGenerator,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function fromView(string $view, array $data, string $filename): PdfDocument
    {
        return $this->pdfGenerator->fromView($view, $data, $filename);
    }

    public function sample(?DateTimeInterface $generatedAt = null): PdfDocument
    {
        $timestamp = $generatedAt ?? new DateTimeImmutable;

        return $this->fromView('pdf.sample', [
            'generatedAt' => $timestamp->format('Y-m-d H:i:s'),
        ], 'nutrisave-muestra.pdf');
    }
}
