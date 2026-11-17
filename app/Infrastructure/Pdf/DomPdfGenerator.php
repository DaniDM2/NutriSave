<?php

declare(strict_types=1);

namespace App\Infrastructure\Pdf;

use App\Domain\Pdf\Contracts\PdfGenerator;
use App\Domain\Pdf\ValueObjects\PdfDocument;
use Barryvdh\DomPDF\Facade\Pdf;

final class DomPdfGenerator implements PdfGenerator
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function fromView(string $view, array $data, string $filename): PdfDocument
    {
        $binary = Pdf::loadView($view, $data)
            ->setPaper('a4')
            ->output();

        return new PdfDocument($filename, $binary);
    }
}
