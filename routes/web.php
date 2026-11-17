<?php

use App\Http\Controllers\PdfController;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Home::class)->name('home');
Route::get('/pdf/sample', [PdfController::class, 'sample'])->name('pdf.sample');
