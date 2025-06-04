<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OnBoardingQuestionController;
use App\Http\Controllers\OnBoardingQuestionOptionController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/add-option',[OnBoardingQuestionOptionController::class,'store'])->name('api.add-option');
Route::post('/delete-option',[OnBoardingQuestionOptionController::class, 'deleteOption'])->name('api.delete-option');
Route::post('/add-question',[OnBoardingQuestionController::class, 'addQuestion'])->name('api.add-question');
Route::post('/delete-question',[OnBoardingQuestionController::class, 'deleteQuestion'])->name('api.delete-question');
Route::put('/update-question/{id}', [OnBoardingQuestionController::class, 'updateQuestion'])->name('api.update-question');
Route::put('/update-option/{id}', [OnBoardingQuestionOptionController::class, 'updateOption'])->name('api.update-option');

Route::get('/create-user',[UserController::class, 'createUser'])->name('api.create-user');
Route::get('/onboarding/questions/{questionSetIndex}/{language}', [OnBoardingQuestionController::class, 'getOnboardingQuestions'])->name('api.onboarding-questions');
Route::post('/onboarding/answers/_calculate', [OnBoardingQuestionController::class, 'saveFirstAnswers'])->name('api.calculate-answers');
Route::post('/onboarding/answers/_finalize', [OnBoardingQuestionController::class, 'saveSecondAnswers'])->name('api.finalize-answers');

Route::get('/firebase-test', [AuthController::class, 'firebaseLogin'])->name('firebase-test');

Route::post('/users/assign-firebase-uid', [UserController::class, 'assignFirebaseUid'])->name('assign-firebase-uid');
Route::get('/users/get-user-recipes', [UserController::class, 'getRecipesByUserIdAndWeek'])->name('get-user-recipes');
Route::get('/users/get-user-recipe', [UserController::class, 'getRecipeByUserIdAndRecipeId'])->name('get-user-recipe');
Route::get('/get-recipes', [RecipeController::class, 'getRecipes'])->name('get-recipes');
Route::post('/update-recipe-status', [RecipeController::class, 'updateRecipeStatus'])->name('update-recipe-status');
Route::post('/update-user-water', [UserController::class, 'updateUserWater'])->name('update-user-water');
Route::get('/faqs', [RecipeController::class, 'getFaqs'])->name('get-faqs');
Route::get('/faq/{id}', [RecipeController::class, 'getFaq'])->name('get-faq');
Route::get('/faqs/categories', [RecipeController::class, 'getFaqCategories'])->name('get-faqs-categories');
Route::get('/users/get-user-calories', [UserController::class, 'getUserCalories'])->name('get-user-calories');
Route::post('/users/add-scope', [UserController::class, 'addScope'])->name('add-scope');
Route::get('/users/get-scopes', [UserController::class, 'getUserScopes'])->name('get-user-scopes');
Route::post('/users/add-photo', [UserController::class, 'addPhoto'])->name('add-photo');
Route::get('/users/get-photos', [UserController::class, 'getUserPhotos'])->name('get-user-photos');
Route::post('/update-recipe-bookmark-status', [RecipeController::class, 'updateRecipeBookmarkStatus'])->name('update-recipe-status');
Route::get('/users/get-water', [UserController::class, 'getUserWater'])->name('get-user-water');
Route::get('/user', [UserController::class, 'getUser'])->name('get-user');
