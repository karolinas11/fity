<?php

namespace App\Repositories;

use App\Models\UserRecipe;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class UserRecipeRepository
{
    public function updateUserRecipeStatus($userId, $recipeId, $status) {
        try {
            $userRecipe = UserRecipe::where('user_id', '=', $userId)
                ->where('recipe_id', '=', $recipeId)
                ->get()
                ->first();
            $userRecipe->status = $status;
            $userRecipe->save();
            return $userRecipe;
        } catch (QueryException $e) {
            Log::error('Can\'t update recipe status: ' . $e->getMessage());
        }
    }

    public function getUserRecipes($userId, $startDate, $endDate) {
        try {
            return UserRecipe::where('user_id', '=', $userId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
        } catch (QueryException $e) {
            Log::error('Can\'t get user recipes: ' . $e->getMessage());
        }
    }
}
