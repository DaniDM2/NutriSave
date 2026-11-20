<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Dashboard;
use App\Livewire\Household\AcceptInvitation;
use App\Livewire\Household\ManageHousehold;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::livewire('/', Dashboard::class)->name('dashboard');
    Route::livewire('/household', ManageHousehold::class)->name('household');
});

Route::livewire('/invitations/{token}', AcceptInvitation::class)->name('household.invitations.accept');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', Login::class)->name('login');
    Route::livewire('/register', Register::class)->name('register');
    Route::livewire('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::livewire('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});
