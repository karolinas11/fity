<?php

namespace App\Repositories;

use App\Models\Recipe;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class RecipeRepository
{
    public function addRecipe($recipeData) {
        try {
            return Recipe::create($recipeData);
        } catch (QueryException $e) {
            Log::error('Can\'t add recipe: ' . $e->getMessage());
        }
    }
}
