<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    TimeTrackerController,
    HabitTrackerController,
    DashboardController,
    FlashCardController
};

// Главная страница с таймером
Route::get('/', function () {
    return view('dashboard'); // Ваш blade-файл с таймером
})->name('home');

// Публичный маршрут для таймера
Route::get('/timer', function () {
    return view('timer');
})->name('timer');

// // Публичный маршрут для флешкарточек
// Route::get('/flashcards', function () {
//     return view('flashcards');
// })->name('flashcards');

Route::get('/flashcards', [FlashCardController::class, 'index'])->name('flashcards');


// Авторизованные маршруты
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/statistics', [DashboardController::class, 'statistics'])->name('dashboard.statistics');
        Route::get('/achievements', [DashboardController::class, 'achievements'])->name('dashboard.achievements');
        Route::get('/settings', [DashboardController::class, 'settings'])->name('dashboard.settings');
    });

    // Трекеры
    Route::resource('time-tracker', TimeTrackerController::class)->only(['index', 'store', 'update']);
    Route::resource('habit-tracker', HabitTrackerController::class)->only(['index', 'store', 'update']);
});