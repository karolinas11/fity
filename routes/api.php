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
