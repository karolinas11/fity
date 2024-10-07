<?php

namespace App\Http\Controllers;

use App\Models\Foodstuff;
use App\Services\RecipeFoodstuffService;
use App\Services\RecipeService;
use Illuminate\Http\Request;

class RecipeController
{
    protected RecipeService $recipeService;
    protected RecipeFoodstuffService $recipeFoodstuffService;

    public function __construct() {
        $this->recipeService = new RecipeService();
        $this->recipeFoodstuffService = new RecipeFoodstuffService();
    }
    public function showAddRecipe() {
        $foodstuffs = Foodstuff::all();
        return view('create-recipe', compact('foodstuffs'));
    }

    public function addRecipe(Request $request) {
        $recipeData = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'short_description' => $request->input('short_description'),
        ];

        $recipe = $this->recipeService->addRecipe($recipeData);
        $recipeFoodstuffData = [
            'recipe_id' => $recipe->id,
            'foodstuff_id' => $request->input('foodstuff_id'),
            'amount' => $request->input('amount'),
        ];
        $recipeFoodstuff = $this->recipeFoodstuffService->addRecipeFoodstuff($recipeFoodstuffData);

        return response()->json($recipe);

    }
}
