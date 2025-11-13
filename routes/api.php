<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;

// Autocomplete search
Route::get('/persons/search', [PersonController::class, 'search'])->name('api.persons.search');

// Show single person by ID
Route::get('/persons/{id}', [PersonController::class, 'show'])->name('api.persons.show');
