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
}
