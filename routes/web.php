<?php

use App\Livewire\ProductManager;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/products', ProductManager::class)->name('products.index');
