<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AthleteController;
use App\Http\Controllers\MetricController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\RoutineController;

Route::get('/', fn () => redirect()->route('dashboard'));

// --- Auth (public) ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/auth/session', [AuthController::class, 'session'])->name('auth.session');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Protected (Firebase session) ---
Route::middleware('firebase.auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Tier 2+ routes register here.

    

// Metrics
Route::get('/metrics', [MetricController::class, 'index'])->name('metrics.index');
Route::post('/metrics', [MetricController::class, 'store'])->name('metrics.store');
Route::put('/metrics/{metric}', [MetricController::class, 'update'])->name('metrics.update');
Route::delete('/metrics/{metric}', [MetricController::class, 'destroy'])->name('metrics.destroy');

// Athletes
Route::get('/athletes', [AthleteController::class, 'index'])->name('athletes.index');
Route::post('/athletes', [AthleteController::class, 'store'])->name('athletes.store');
Route::get('/athletes/{athlete}', [AthleteController::class, 'show'])->name('athletes.show');
Route::put('/athletes/{athlete}', [AthleteController::class, 'update'])->name('athletes.update');
Route::delete('/athletes/{athlete}', [AthleteController::class, 'destroy'])->name('athletes.destroy');

// Measurements (nested under athlete)
Route::post('/athletes/{athlete}/measurements', [MeasurementController::class, 'store'])
    ->name('athletes.measurements.store');
Route::delete('/athletes/{athlete}/measurements/{measurement}', [MeasurementController::class, 'destroy'])
    ->name('athletes.measurements.destroy');
Route::get('/athletes/{athlete}/history/{metric}', [MeasurementController::class, 'history'])
    ->name('athletes.history');



Route::get('/exercises', [ExerciseController::class, 'index'])->name('exercises.index');
Route::post('/exercises', [ExerciseController::class, 'store'])->name('exercises.store');
Route::put('/exercises/{exercise}', [ExerciseController::class, 'update'])->name('exercises.update');
Route::delete('/exercises/{exercise}', [ExerciseController::class, 'destroy'])->name('exercises.destroy');



// Rules
Route::get('/rules', [RuleController::class, 'index'])->name('rules.index');
Route::post('/rules', [RuleController::class, 'store'])->name('rules.store');
Route::put('/rules/{rule}', [RuleController::class, 'update'])->name('rules.update');
Route::delete('/rules/{rule}', [RuleController::class, 'destroy'])->name('rules.destroy');

// Recommendations (engine output for one athlete)
Route::get('/athletes/{athlete}/recommendations', [RecommendationController::class, 'forAthlete'])
    ->name('athletes.recommendations');


Route::get('/routines', [RoutineController::class, 'index'])->name('routines.index');
Route::post('/routines', [RoutineController::class, 'store'])->name('routines.store');
Route::get('/routines/{routine}', [RoutineController::class, 'show'])->name('routines.show');
Route::delete('/routines/{routine}', [RoutineController::class, 'destroy'])->name('routines.destroy');

Route::patch('/routines/{routine}/status', [RoutineController::class, 'updateStatus'])->name('routines.status');
Route::post('/routines/{routine}/autofill', [RoutineController::class, 'autofill'])->name('routines.autofill');

Route::post('/routines/{routine}/items', [RoutineController::class, 'addItem'])->name('routines.items.add');
Route::delete('/routines/{routine}/items/{item}', [RoutineController::class, 'removeItem'])->name('routines.items.remove');
Route::patch('/routines/{routine}/items/{item}/toggle', [RoutineController::class, 'toggleItem'])->name('routines.items.toggle');


});
