<?php

declare(strict_types=1);

namespace App\Infrastructure\Queue;

use App\Application\Pdf\GenerateDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateSamplePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(GenerateDocument $generateDocument): void
    {
        $document = $generateDocument->sample();

        Storage::disk('local')->put('pdf/'.$document->filename, $document->binary);
    }
}
