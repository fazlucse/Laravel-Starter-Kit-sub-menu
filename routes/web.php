<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\MenuController;
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

});
require __DIR__.'/settings.php';
