<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GlassController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/test-layout', function () {
    return view('test-layout');
});

// Language switching
Route::get('/locale/{locale}', [App\Http\Controllers\LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Patient routes
    Route::resource('patients', PatientController::class);

    // Exam routes
    Route::resource('exams', ExamController::class);
    Route::get('/exams/{exam}/prescription', [ExamController::class, 'prescription'])->name('exams.prescription');

    // Glass routes
    Route::resource('glasses', GlassController::class);
    Route::patch('/glasses/{glass}/status', [GlassController::class, 'updateStatus'])->name('glasses.status');

    // Sale routes
    Route::resource('sales', SaleController::class);

    // Expense routes
    Route::resource('expenses', ExpenseController::class);

    // Report routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/patients', [ReportController::class, 'patients'])->name('patients');
        Route::get('/glasses', [ReportController::class, 'glasses'])->name('glasses');
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
    });

    // User management routes
    Route::resource('users', UserController::class);

    // Stock management routes
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/create', [StockController::class, 'create'])->name('create');
        Route::post('/', [StockController::class, 'store'])->name('store');
        Route::get('/{stock}', [StockController::class, 'show'])->name('show');
        Route::get('/{stock}/edit', [StockController::class, 'edit'])->name('edit');
        Route::put('/{stock}', [StockController::class, 'update'])->name('update');
        Route::delete('/{stock}', [StockController::class, 'destroy'])->name('destroy');

        // Stock movement routes
        Route::get('/{stock}/add', [StockController::class, 'addStock'])->name('add');
        Route::post('/{stock}/add', [StockController::class, 'processAddStock'])->name('add.process');
        Route::get('/{stock}/remove', [StockController::class, 'removeStock'])->name('remove');
        Route::post('/{stock}/remove', [StockController::class, 'processRemoveStock'])->name('remove.process');
        Route::get('/{stock}/adjust', [StockController::class, 'adjustStock'])->name('adjust');
        Route::post('/{stock}/adjust', [StockController::class, 'processAdjustStock'])->name('adjust.process');

        // Stock movements history
        Route::get('/movements/list', [StockController::class, 'movements'])->name('movements');
    });
});

require __DIR__ . '/auth.php';
