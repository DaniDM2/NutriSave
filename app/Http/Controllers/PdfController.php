<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Pdf\GenerateDocument;
use Symfony\Component\HttpFoundation\Response;

class PdfController extends Controller
{
    public function sample(GenerateDocument $generateDocument): Response
    {
        $document = $generateDocument->sample();

        return response($document->binary, 200, [
            'Content-Type' => $document->mime,
            'Content-Disposition' => 'attachment; filename="'.$document->filename.'"',
        ]);
    }
}
