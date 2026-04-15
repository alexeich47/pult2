<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProfileController;
use App\Support\PultEnums;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Home'))->name('dashboard');

    Route::get('/personnel', [EmployeeController::class, 'index'])->name('personnel.index');
    Route::post('/personnel', [EmployeeController::class, 'store'])->name('personnel.store');
    Route::put('/personnel/{employee}', [EmployeeController::class, 'update'])->name('personnel.update');
    Route::delete('/personnel/{employee}', [EmployeeController::class, 'destroy'])->name('personnel.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/locale/{locale}', function (string $locale) {
        abort_unless(in_array($locale, PultEnums::supportedLocales(), true), 404);
        session(['locale' => $locale]);

        return back();
    })->name('locale.switch');
});

require __DIR__.'/auth.php';
