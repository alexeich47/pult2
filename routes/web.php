<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\IdeaController;
use App\Http\Controllers\InstructionController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\OkrController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiskEntryController;
use App\Http\Controllers\RndProjectController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\UnitDashboardController;
use App\Models\Employee;
use App\Models\Unit;
use App\Support\PultEnums;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// Locale switcher — public so guests can pick their language before login.
Route::post('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, PultEnums::supportedLocales(), true), 404);
    session(['locale' => $locale]);

    return back();
})->name('locale.switch');

Route::post('/context/{unitId}', function (string $unitId) {
    if ($unitId === 'all') {
        session()->forget('active_unit_id');
    } else {
        abort_unless(Unit::where('id', $unitId)->exists(), 404);
        session(['active_unit_id' => $unitId]);
    }

    return back();
})->middleware('auth')->name('context.switch');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/tickets', fn () => Inertia::render('Tickets/Index'))->name('tickets.index');

    Route::get('/structure', [StructureController::class, 'index'])->name('structure.index');
    Route::post('/structure', [StructureController::class, 'store'])->name('structure.store');
    Route::put('/structure/{unit}', [StructureController::class, 'update'])->name('structure.update');
    Route::delete('/structure/{unit}', [StructureController::class, 'destroy'])->name('structure.destroy');

    Route::get('/units/{unit}', UnitDashboardController::class)->name('units.show');

    Route::get('/personnel', [EmployeeController::class, 'index'])->name('personnel.index');
    Route::post('/personnel', [EmployeeController::class, 'store'])->name('personnel.store');
    Route::put('/personnel/{employee}', [EmployeeController::class, 'update'])->name('personnel.update');
    Route::delete('/personnel/{employee}', [EmployeeController::class, 'destroy'])->name('personnel.destroy');

    Route::get('/hiring', fn () => Inertia::render('Hiring/Index', [
        'vacancies' => Employee::query()->with('unit')->where('status', 'vacancy')->orderBy('unit_id')->paginate(20),
        'allUnits' => Unit::orderBy('sort_order')->get(),
        'departments' => PultEnums::departments(),
        'can' => ['create' => request()->user()?->can('create', Employee::class)],
    ]))->name('hiring.index');

    Route::get('/former', fn () => Inertia::render('Former/Index', [
        'employees' => Employee::query()->with(['unit', 'manager'])->where('status', 'fired')->orderByDesc('updated_at')->paginate(20),
        'allUnits' => Unit::orderBy('sort_order')->get(),
    ]))->name('former.index');

    Route::get('/ideas', [IdeaController::class, 'index'])->name('ideas.index');
    Route::post('/ideas', [IdeaController::class, 'store'])->name('ideas.store');
    Route::post('/ideas/bulk-delete', [IdeaController::class, 'bulkDestroy'])->name('ideas.bulkDestroy');
    Route::get('/ideas/{idea}', [IdeaController::class, 'show'])->name('ideas.show');
    Route::put('/ideas/{idea}', [IdeaController::class, 'update'])->name('ideas.update');
    Route::patch('/ideas/{idea}', [IdeaController::class, 'patch'])->name('ideas.patch');
    Route::delete('/ideas/{idea}', [IdeaController::class, 'destroy'])->name('ideas.destroy');

    Route::post('/personnel/bulk-delete', [EmployeeController::class, 'bulkDestroy'])->name('personnel.bulkDestroy');

    Route::get('/risks', [RiskEntryController::class, 'index'])->name('risks.index');
    Route::post('/risks', [RiskEntryController::class, 'store'])->name('risks.store');
    Route::post('/risks/bulk-delete', [RiskEntryController::class, 'bulkDestroy'])->name('risks.bulkDestroy');
    Route::get('/risks/{risk_entry}', [RiskEntryController::class, 'show'])->name('risks.show');
    Route::put('/risks/{risk_entry}', [RiskEntryController::class, 'update'])->name('risks.update');
    Route::delete('/risks/{risk_entry}', [RiskEntryController::class, 'destroy'])->name('risks.destroy');

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::post('/services/bulk-delete', [ServiceController::class, 'bulkDestroy'])->name('services.bulkDestroy');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    Route::get('/instructions', [InstructionController::class, 'index'])->name('instructions.index');
    Route::post('/instructions', [InstructionController::class, 'store'])->name('instructions.store');
    Route::put('/instructions/{instruction}', [InstructionController::class, 'update'])->name('instructions.update');
    Route::delete('/instructions/{instruction}', [InstructionController::class, 'destroy'])->name('instructions.destroy');

    Route::get('/okr', [OkrController::class, 'index'])->name('okr.index');
    Route::post('/okr', [OkrController::class, 'store'])->name('okr.store');
    Route::put('/okr/{okr}', [OkrController::class, 'update'])->name('okr.update');
    Route::delete('/okr/{okr}', [OkrController::class, 'destroy'])->name('okr.destroy');

    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::put('/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
    Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');

    Route::get('/processes', [ProcessController::class, 'index'])->name('processes.index');
    Route::post('/processes', [ProcessController::class, 'store'])->name('processes.store');
    Route::put('/processes/{process}', [ProcessController::class, 'update'])->name('processes.update');
    Route::delete('/processes/{process}', [ProcessController::class, 'destroy'])->name('processes.destroy');
    Route::post('/processes/bulk-delete', [ProcessController::class, 'bulkDestroy'])->name('processes.bulkDestroy');

    Route::post('/rnd/bulk-delete', [RndProjectController::class, 'bulkDestroy'])->name('rnd.bulkDestroy');
    Route::get('/rnd', [RndProjectController::class, 'index'])->name('rnd.index');
    Route::post('/rnd', [RndProjectController::class, 'store'])->name('rnd.store');
    Route::put('/rnd/{rndProject}', [RndProjectController::class, 'update'])->name('rnd.update');
    Route::delete('/rnd/{rndProject}', [RndProjectController::class, 'destroy'])->name('rnd.destroy');

    // Reference content (static, i18n-driven)
    Route::get('/archive', ArchiveController::class)->name('archive.index');
    Route::get('/activity-log', ActivityLogController::class)->name('activity-log.index');
    Route::get('/codex', fn () => Inertia::render('Codex/Index'))->name('codex.index');
    Route::get('/dictionary', fn () => Inertia::render('Dictionary/Index'))->name('dictionary.index');
    Route::get('/info', fn () => Inertia::render('Info/Index'))->name('info.index');

    // Placeholder routes — scaffolded for future phases. Each uses the shared
    // Placeholder.vue component with per-route i18n keys.
    $placeholders = [
        'strategy' => ['icon' => '🎯'],
        'sla' => ['icon' => '⏱'],
    ];
    foreach ($placeholders as $slug => $config) {
        Route::get("/{$slug}", fn () => Inertia::render('Placeholder', [
            'titleKey' => "page.{$slug}.title",
            'subtitleKey' => "page.{$slug}.sub",
            'icon' => $config['icon'],
        ]))->name("{$slug}.index");
    }
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
