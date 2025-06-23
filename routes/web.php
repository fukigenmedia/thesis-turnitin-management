<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::redirect('settings', 'settings/profile');
    Route::prefix('settings')->name('settings.')->group(function () {
        Volt::route('/profile', 'settings.profile')->name('profile');
        Volt::route('/password', 'settings.password')->name('password');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Volt::route('/', 'pages.users.index')->name('index');
            Volt::route('/create', 'pages.users.create')->name('create');
            Volt::route('/{user}/edit', 'pages.users.edit')->name('edit');
        });

        Route::prefix('sliders')->name('sliders.')->group(function () {
            Volt::route('/', 'pages.sliders.index')->name('index');
            Volt::route('create', 'pages.sliders.create')->name('create');
            Volt::route('{slider}/edit', 'pages.sliders.edit')->name('edit');
        });
    });
});

require __DIR__ . '/auth.php';
