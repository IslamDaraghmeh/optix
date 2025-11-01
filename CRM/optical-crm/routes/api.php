<?php

use App\Http\Controllers\ExpenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Expense API routes
Route::middleware('auth:sanctum')->prefix('expenses')->group(function () {
    Route::get('/', [ExpenseController::class, 'apiIndex'])->name('api.expenses.index');
    Route::post('/', [ExpenseController::class, 'apiStore'])->name('api.expenses.store');
    Route::get('/{expense}', [ExpenseController::class, 'show'])->name('api.expenses.show');
    Route::put('/{expense}', [ExpenseController::class, 'apiUpdate'])->name('api.expenses.update');
    Route::delete('/{expense}', [ExpenseController::class, 'apiDestroy'])->name('api.expenses.destroy');
});
