<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\IdeaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiskEntryController;
use App\Http\Controllers\ServiceController;
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

    Route::get('/ideas', [IdeaController::class, 'index'])->name('ideas.index');
    Route::post('/ideas', [IdeaController::class, 'store'])->name('ideas.store');
    Route::get('/ideas/{idea}', [IdeaController::class, 'show'])->name('ideas.show');
    Route::put('/ideas/{idea}', [IdeaController::class, 'update'])->name('ideas.update');
    Route::delete('/ideas/{idea}', [IdeaController::class, 'destroy'])->name('ideas.destroy');

    Route::get('/risks', [RiskEntryController::class, 'index'])->name('risks.index');
    Route::post('/risks', [RiskEntryController::class, 'store'])->name('risks.store');
    Route::get('/risks/{risk_entry}', [RiskEntryController::class, 'show'])->name('risks.show');
    Route::put('/risks/{risk_entry}', [RiskEntryController::class, 'update'])->name('risks.update');
    Route::delete('/risks/{risk_entry}', [RiskEntryController::class, 'destroy'])->name('risks.destroy');

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // Reference content (static, i18n-driven)
    Route::get('/codex', fn () => Inertia::render('Codex/Index'))->name('codex.index');
    Route::get('/dictionary', fn () => Inertia::render('Dictionary/Index'))->name('dictionary.index');
    Route::get('/info', fn () => Inertia::render('Info/Index'))->name('info.index');
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
