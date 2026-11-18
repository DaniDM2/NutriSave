<?php

use App\Http\Controllers\PdfController;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Home::class)->name('home');
Route::get('/pdf/sample', [PdfController::class, 'sample'])->name('pdf.sample');
Route::get('/reset-password/{token}', fn () => abort(404))->name('password.reset');
