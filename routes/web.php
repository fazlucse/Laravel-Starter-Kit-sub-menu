<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveAllotmentController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\MovementRegisterController;

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

    Route::middleware(['auth'])->prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/create', [AttendanceController::class, 'create'])->name('create');
        Route::post('/', [AttendanceController::class, 'store'])->name('store');
        Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
        Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
        Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
        Route::get('/{attendance}', [AttendanceController::class, 'show'])->name('show');
    });

    Route::middleware(['auth'])->prefix('leave-allotments')->group(function () {
        Route::get('/', [LeaveAllotmentController::class, 'index'])->name('leave_allotments.index');
        Route::get('/create', [LeaveAllotmentController::class, 'create'])->name('leave_allotments.create');
        Route::post('/', [LeaveAllotmentController::class, 'store'])->name('leave_allotments.store'); // POST /leave-allotments
        Route::get('/{id}/edit', [LeaveAllotmentController::class, 'edit'])->name('leave_allotments.edit');
        Route::put('/{id}', [LeaveAllotmentController::class, 'update'])->name('leave_allotments.update'); // PUT /leave-allotments/{id}
        Route::delete('/{id}', [LeaveAllotmentController::class, 'destroy'])->name('leave_allotments.destroy');
    });
    Route::middleware(['auth'])->prefix('leave-request')->group(function() {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('leave_requests.index');
        Route::get('/create', [LeaveRequestController::class, 'create'])->name('leave_requests.create');
        Route::post('/', [LeaveRequestController::class, 'store'])->name('leave_requests.store');
    });


    Route::middleware(['auth'])->prefix('holidays')->group(function () {
        Route::get('/', [HolidayController::class, 'index'])->name('holidays.index');
        Route::get('/create', [HolidayController::class, 'create'])->name('holidays.create');
        Route::post('/', [HolidayController::class, 'store'])->name('holidays.store');
        Route::get('/{holiday}', [HolidayController::class, 'show'])->name('holidays.show');
        Route::get('/{holiday}/edit', [HolidayController::class, 'edit'])->name('holidays.edit');
        Route::put('/{holiday}', [HolidayController::class, 'update'])->name('holidays.update');
        Route::delete('/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
    });


    Route::middleware(['auth'])->prefix('movement-registers')->group(function () {
        Route::get('/', [MovementRegisterController::class, 'index'])->name('movement-registers.index');
        Route::get('/create', [MovementRegisterController::class, 'create'])->name('movement-registers.create');
        Route::post('/', [MovementRegisterController::class, 'store'])->name('movement-registers.store');
        Route::get('/{movementRegister}', [MovementRegisterController::class, 'show'])->name('movement-registers.show');
        Route::get('/{movementRegister}/edit', [MovementRegisterController::class, 'edit'])->name('movement-registers.edit');
        Route::put('/{movementRegister}', [MovementRegisterController::class, 'update'])->name('movement-registers.update');
        Route::delete('/{movementRegister}', [MovementRegisterController::class, 'destroy'])->name('movement-registers.destroy');
    });
    // Route::get('/employees/persons/search', [PersonController::class, 'search']);
// Route::get('/companies', [CompanyController::class, 'index']);
// Route::get('/divisions', [DivisionController::class, 'index']);
// Route::get('/departments', [DepartmentController::class, 'index']);
// Route::get('/designations/search', [DesignationController::class, 'search']);
});
require __DIR__.'/settings.php';
