<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\MovementRegisterController;

// Autocomplete search
Route::get('/persons/search', [PersonController::class, 'search'])->name('api.persons.search');

// Show single person by ID
Route::get('/persons/{id}', [PersonController::class, 'show'])->name('api.persons.show');
Route::get('/movement-settings', [MovementRegisterController::class, 'getSettings']);
