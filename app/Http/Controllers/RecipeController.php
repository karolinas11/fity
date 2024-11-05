<?php

namespace App\Http\Controllers;

use App\DataTables\RecipeDataTable;
use App\DataTables\RecipesDataTable;
use App\Models\Foodstuff;
use App\Models\Recipe;
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
            'type' => $request->input('type')
        ];
        $recipe = $this->recipeService->addRecipe($recipeData);
        $this->recipeFoodstuffService->addRecipeFoodstuff($recipe->id, $request->input('foodstuffs'));
        return response()->json($recipe);
    }

    public function showRecipesList(RecipeDataTable $dataTable) {
        return $dataTable->render('recipes-list');
    }

    public function showRecipeEdit($id) {
        $recipe = Recipe::find($id);
        $foodstuffs = Foodstuff::all();
        $recipeFoodstuffs = $this->recipeFoodstuffService->getRecipeFoodstuffs($id);
        return view('edit-recipe', compact('recipe', 'foodstuffs', 'recipeFoodstuffs'));
    }

    public function editRecipe(Request $request, $id) {
        $recipeData = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'short_description' => $request->input('short_description'),
            'type' => $request->input('type')
        ];
        $recipe = $this->recipeService->editRecipe($recipeData, $id);
        $this->recipeFoodstuffService->deleteRecipeFoodstuff($recipe->id);
        $this->recipeFoodstuffService->addRecipeFoodstuff($recipe->id, $request->input('foodstuffs'));
        return redirect()->route('show-recipes-list');
    }

    public function deleteRecipe($id) {
        $recipe = Recipe::find($id);
        $recipe->delete();
        return redirect()->route('show-recipes-list');
    }

    public function printRecipes() {
        $recipes = Recipe::all();
        //dd($recipes);
        $recipesFinal = [];
        foreach ($recipes as $recipe) {
            $recipeFoodstuffs = $this->recipeFoodstuffService->getRecipeFoodstuffs($recipe->id);
            $calories = 0;
            $proteins = 0;
            $fats = 0;
            $carbohydrates = 0;

            foreach ($recipeFoodstuffs as $recipeFoodstuff) {
                if($recipeFoodstuff->proteins_holder == 0) {
                    $calories += Foodstuff::where('id', $recipeFoodstuff->foodstuff_id)->first()->calories * $recipeFoodstuff->amount / 100;
                    $proteins += Foodstuff::where('id', $recipeFoodstuff->foodstuff_id)->first()->proteins * $recipeFoodstuff->amount / 100;
                    $fats += Foodstuff::where('id', $recipeFoodstuff->foodstuff_id)->first()->fats * $recipeFoodstuff->amount / 100;
                    $carbohydrates += Foodstuff::where('id', $recipeFoodstuff->foodstuff_id)->first()->carbohydrates * $recipeFoodstuff->amount / 100;
                }
            }

            $recipeCalories = 0;
            $recipeProteins = 0;
            $recipeFats = 0;
            $recipeCarbohydrates = 0;

            foreach ($recipeFoodstuffs as $recipeFoodstuff) {
                $min = 100;
                $max = 500;
                if($recipeFoodstuff->proteins_holder != 0) {
                    while($min <= $max) {
                        $recipeCalories = $min * Foodstuff::where('id', $recipeFoodstuff->foodstuff_id)->first()->calories / 100;
                        $recipeProteins = $min * Foodstuff::where('id', $recipeFoodstuff->foodstuff_id)->first()->proteins / 100;
                        $recipeFats = $min * Foodstuff::where('id', $recipeFoodstuff->foodstuff_id)->first()->fats / 100;
                        $recipeCarbohydrates = $min * Foodstuff::where('id', $recipeFoodstuff->foodstuff_id)->first()->carbohydrates / 100;
                        $recipeFinal = [
                            'id' => $recipe->id,
                            'name' => $recipe->name . ' - ' . $min,
                            'category' => $recipe->type,
                            'calories' => $recipeCalories + $calories,
                            'proteins' => $recipeProteins + $proteins,
                            'fats' => $recipeFats + $fats,
                            'carbohydrates' => $recipeCarbohydrates + $carbohydrates
                        ];
                        array_push($recipesFinal, $recipeFinal);
                        $min += 20;
                    }
                }
            }

            $recipeFinal = [
                'id' => $recipe->id,
                'name' => $recipe->name,
                'category' => $recipe->type,
                'calories' => $recipeCalories + $calories,
                'proteins' => $recipeProteins + $proteins,
                'fats' => $recipeFats + $fats,
                'carbohydrates' => $recipeCarbohydrates + $carbohydrates
            ];
            array_push($recipesFinal, $recipeFinal);
        }

        foreach ($recipesFinal as $key => $recipe) {
            echo $key . ',' . $recipe['id'] . ',' . $recipe['name'] . ',' . $recipe['category'] . ',' . $recipe['calories'] . ',' . $recipe['proteins'] . ',' . $recipe['fats'] . ',' . $recipe['carbohydrates'] . "<br>";
        }
    }

    private function calculateCalories($proteins, $fats, $carbohydrates) {
        return $proteins * 4 + $fats * 9 + $carbohydrates * 4;
    }
}
