<?php

use App\Http\Controllers\FoodstuffCategoryController;
use App\Http\Controllers\FoodstuffController;
use App\Http\Controllers\OnBoardingQuestionController;
use App\Http\Controllers\onBoardingQuestionOptionController;
use App\Http\Controllers\RecipeController;
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

Route::get('/add-recipe-form', [RecipeController::class, 'showAddRecipe'])->name('show-add-recipe');
Route::post('/add-recipe', [RecipeController::class, 'addRecipe'])->name('add-recipe');

Route::get('/user/{userId}', [UserController::class, 'assignRecipesToUser'])->name('assign-recipes-to-user');
//user/edit preko ajaksa radimo izmeni dugme
Route::post('/user/edit', [UserController::class, 'editUser']);

Route::get('/recipes', [RecipeController::class, 'showRecipesList'])->name('show-recipes-list');
Route::get('/foodstuffs', [FoodstuffController::class, 'showFoodstuffsList'])->name('show-foodstuffs-list');

Route::get('/foodstuff/{foodstuffId}/edit', [FoodstuffController::class, 'showFoodstuffEdit'])->name('show-foodstuff-edit');
Route::post('/foodstuff/{foodstuffId}/edit', [FoodstuffController::class, 'editFoodstuff'])->name('edit-foodstuff');
Route::delete('/foodstuff/{foodstuffId}/delete', [FoodstuffController::class, 'deleteFoodstuff'])->name('delete-foodstuff');

Route::get('/recipe/{recipeId}/edit', [RecipeController::class, 'showRecipeEdit'])->name('show-recipe-edit');
Route::post('/recipe/{recipeId}/edit', [RecipeController::class, 'editRecipe'])->name('edit-recipe');
Route::delete('/recipe/{recipeId}/delete', [RecipeController::class, 'deleteRecipe'])->name('delete-recipe');

Route::get('/ide-gas', [RecipeController::class, 'printRecipes'])->name('ide-gas');
Route::get('/ide-gas2', [RecipeController::class, 'printFoodstuffs'])->name('ide-gas');
Route::get('/test-curl', [RecipeController::class, 'testCurl'])->name('test-curl');

Route::get('/users', [UserController::class, 'showUsersList'])->name('show-users-list');


Route::get('/boarding-question',[OnBoardingQuestionController::class, 'index']);

Route::post('/api/add-option',[OnBoardingQuestionOptionController::class,'store'])->name('api.add-option');
Route::post('/api/delete-option',[OnBoardingQuestionOptionController::class, 'deleteOption'])->name('api.delete-option');
Route::post('/api/add-question',[OnBoardingQuestionController::class, 'addQuestion'])->name('api.add-question');
Route::post('/api/delete-question',[OnBoardingQuestionController::class, 'deleteQuestion'])->name('api.delete-question');
Route::put('/api/update-question/{id}', [OnBoardingQuestionController::class, 'updateQuestion'])->name('api.update-question');
Route::put('/api/update-option/{id}', [OnBoardingQuestionOptionController::class, 'updateOption'])->name('api.update-option');
Route::post('/api/create-user',[UserController::class, 'createUser'])->name('api.create-user');
Route::get('/api/onboarding/questions/{questionSetIndex}/{language}', [OnBoardingQuestionController::class, 'getOnboardingQuestions'])->name('api.onboarding-questions');
