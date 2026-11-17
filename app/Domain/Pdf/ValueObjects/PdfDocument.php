<?php

declare(strict_types=1);

namespace App\Domain\Pdf\ValueObjects;

final readonly class PdfDocument
{
    public function __construct(
        public string $filename,
        public string $binary,
        public string $mime = 'application/pdf',
    ) {}
}
