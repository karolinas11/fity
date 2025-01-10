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

    public function editRecipe($recipeData, $id) {
        try {
            $recipe = Recipe::find($id);
            $recipe->name = $recipeData['name'];
            $recipe->description = $recipeData['description'];
            $recipe->short_description = $recipeData['short_description'];
            $recipe->insulin = $recipeData['insulin'];
            $recipe->type = $recipeData['type'];
            $recipe->save();
            return $recipe;
        } catch (QueryException $e) {
            Log::error('Can\'t edit recipe: ' . $e->getMessage());
        }
    }

    public function getRecipeFoodstuffs($id) {
        return Recipe::find($id)->foodstuffs;
    }
}
