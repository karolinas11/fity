<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OnBoardingQuestionController;
use App\Http\Controllers\OnBoardingQuestionOptionController;
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
Route::post('/onboarding/answers/_calculate', function(Request $request) {
   \Illuminate\Support\Facades\Log::error('TEST LOG', $request->all());
   return response()->json(['success' => true], 200);
});

Route::get('/firebase-test', [AuthController::class, 'firebaseLogin'])->name('firebase-test');
