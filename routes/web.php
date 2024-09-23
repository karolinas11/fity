<?php

use App\Http\Controllers\FoodstuffCategoryController;
use App\Http\Controllers\FoodstuffController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/add-user-form', [UserController::class, 'showAddUser'])->name('show-add-user');
Route::post('/add-user', [UserController::class, 'addUser'])->name('add-user');

Route::get('/add-foodstuff-category-form', [FoodstuffCategoryController::class, 'showAddFoodstuffCategory'])->name('show-add-foodstuff-category');
Route::post('/add-foodstuff-category', [FoodstuffCategoryController::class, 'addFoodstuffCategory'])->name('add-foodstuff-category');

Route::get('/add-foodstuff-form', [FoodstuffController::class, 'showAddFoodstuff'])->name('show-add-foodstuff');
Route::post('/add-foodstuff', [FoodstuffController::class, 'addFoodstuff'])->name('add-foodstuff');
