<?php

declare(strict_types=1);

namespace App\Domain\Pdf\Contracts;

use App\Domain\Pdf\ValueObjects\PdfDocument;

interface PdfGenerator
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function fromView(string $view, array $data, string $filename): PdfDocument;
}
