<?php

use App\Http\Controllers\ExportController;
use App\Livewire\ProductManager;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/products', ProductManager::class)
    ->middleware(['auth'])
    ->name('products.index');

Route::get('/categories', \App\Livewire\CategoryManager::class)
    ->middleware(['auth'])
    ->name('categories.index');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Export routes
Route::middleware(['auth'])->group(function () {
    Route::get('/export/products/csv', [ExportController::class, 'productsCSV'])->name('export.products.csv');
    Route::get('/export/products/excel', [ExportController::class, 'productsExcel'])->name('export.products.excel');
    Route::get('/export/categories/csv', [ExportController::class, 'categoriesCSV'])->name('export.categories.csv');
    Route::get('/export/categories/excel', [ExportController::class, 'categoriesExcel'])->name('export.categories.excel');
});

require __DIR__.'/auth.php';
