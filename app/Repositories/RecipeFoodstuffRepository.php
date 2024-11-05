<?php

namespace App\Repositories;

use App\Models\RecipeFoodstuff;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class RecipeFoodstuffRepository
{
    public function addRecipeFoodstuff($recipeId, $foodstuffs) {
        try {
            foreach ($foodstuffs as $foodstuff) {
                RecipeFoodstuff::create([
                    'recipe_id' => $recipeId,
                    'foodstuff_id' => $foodstuff['foodstuff_id'],
                    'amount' => $foodstuff['amount'],
                    'proteins_holder' => $foodstuff['proteins_holder'],
                    'fats_holder' => $foodstuff['fats_holder'],
                    'carbohydrates_holder' => $foodstuff['carbohydrates_holder'],
                ]);
            }
        } catch (QueryException $e) {
            Log::error('Can\'t add recipeFoodstuff: ' . $e->getMessage());
        }
    }

    public function getRecipeFoodstuffs($id) {
        try {
            return RecipeFoodstuff::where('recipe_id', $id)->get();
        } catch (QueryException $e) {
            Log::error('Can\'t get recipeFoodstuffs: ' . $e->getMessage());
        }
    }

    public function deleteRecipeFoodstuff($recipeId) {
        try {
            RecipeFoodstuff::where('recipe_id', $recipeId)->delete();
        } catch (QueryException $e) {
            Log::error('Can\'t delete recipeFoodstuffs: ' . $e->getMessage());
        }
    }

}
