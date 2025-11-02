<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PersonController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return inertia('Dashboard');
    })->name('dashboard');
    Route::get('/menus.index', [MenuController::class, 'index'])->name('menus.index');
    Route::get('/menus.create', [MenuController::class, 'create'])->name('menus.create');
    Route::resource('menus', MenuController::class);
    Route::match(['get', 'post'], '/people.index', [PersonController::class, 'index'])->name('people.index');
    Route::get('/people.create', [PersonController::class, 'create'])->name('people.create');
    Route::resource('people', PersonController::class);
});
require __DIR__.'/settings.php';
