<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\EmployeeController;


Route::get('/', function () {
  return redirect()->route('login');
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
    Route::post('/people/{person}', [PersonController::class, 'update'])->name('people.update');
    Route::resource('people', PersonController::class);

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees.store', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');

    // Route::get('/employees/persons/search', [PersonController::class, 'search']);
// Route::get('/companies', [CompanyController::class, 'index']);
// Route::get('/divisions', [DivisionController::class, 'index']);
// Route::get('/departments', [DepartmentController::class, 'index']);
// Route::get('/designations/search', [DesignationController::class, 'search']);
});
require __DIR__.'/settings.php';
