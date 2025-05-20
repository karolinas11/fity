<?php

namespace App\Services;

use App\Http\Controllers\UserController;
use App\Models\Foodstuff;
use App\Models\FoodstuffCategory;
use App\Models\Recipe;
use App\Models\UserRecipe;
use App\Repositories\UserRecipeRepository;
use Illuminate\Support\Facades\Log;

class UserRecipeService
{
    protected UserRecipeRepository $userRecipeRepository;

    public function __construct() {
        $this->userRecipeRepository = new UserRecipeRepository();
    }

    public function updateUserRecipeStatus($userId, $recipeId, $status) {
        return $this->userRecipeRepository->updateUserRecipeStatus($userId, $recipeId, $status);
    }

    public function getUserRecipesByDate($userId, $startDate, $endDate) {
        $recipes = $this->userRecipeRepository->getUserRecipes($userId, $startDate, $endDate);
        foreach ($recipes as &$recipe) {
            $r = Recipe::where('id', $recipe->recipe_id)->first();
            $recipe->foodstuffs = $recipe->foodstuffs;
            $recipe->type = $r->type;
            $recipe->name = $r->name;
            $recipe->id = $r->id;
            $recipe->featured_image = $r->featured_image;
            $description = str_replace('\n', "\n", $r->description);
            $recipe->steps = preg_split('/\r\n|\r|\n/', $description);
            $recipe->steps = array_filter($recipe->steps, fn($step) => trim($step) !== '');
            $cal = 0;
            $prot = 0;
            $fat = 0;
            $ch = 0;
            foreach ($recipe->foodstuffs as &$foodstuff) {
                $f = Foodstuff::where('id', $foodstuff->foodstuff_id)->get()[0];
                $cal += $foodstuff->amount * ($f->calories / 100);
                $prot += $foodstuff->amount * ($f->proteins / 100);
                $fat += $foodstuff->amount * ($f->fats / 100);
                $ch += $foodstuff->amount * ($f->carbohydrates / 100);
                $foodstuff->foodstuff_category = FoodstuffCategory::where('id', $f->foodstuff_category_id)->get()[0]->name;
                $foodstuff->name = $f->name;
            }

            $recipe->calAmount = $cal;
            $recipe->proteinAmount = $prot;
            $recipe->fatsAmount = $fat;
            $recipe->chAmount = $ch;
        }

        $recipesByDate = $recipes->groupBy(function ($recipe) {
            return \Carbon\Carbon::parse($recipe->date)->format('Y-m-d');
        });
        $recipesByDate = $recipesByDate->sortKeys();

        $recipesArray = $recipesByDate->map(function ($items, $date) {
            return [
                'date' => $date,
                'recipes' => $items->values(),
            ];
        })->values();

        return $recipesArray;
    }

    public function getUserRecipeByUserIdAndRecipeId($userId, $recipeId) {
        $recipe = UserRecipe::where('user_id', $userId)
            ->where('recipe_id', $recipeId)
            ->get()
            ->first();
        $r = Recipe::where('id', $recipe->recipe_id)->first();
        $recipe->foodstuffs = $recipe->foodstuffs;
        $recipe->type = $r->type;
        $recipe->name = $r->name;
        $recipe->featured_image = $r->featured_image;
        $description = str_replace('\n', "\n", $r->description);
        $recipe->steps = preg_split('/\r\n|\r|\n/', $description);
        $recipe->steps = array_filter($recipe->steps, fn($step) => trim($step) !== '');
        $cal = 0;
        $prot = 0;
        $fat = 0;
        $ch = 0;
        foreach ($recipe->foodstuffs as &$foodstuff) {
            $f = Foodstuff::where('id', $foodstuff->foodstuff_id)->get()[0];
            $cal += $foodstuff->amount * ($f->calories / 100);
            $prot += $foodstuff->amount * ($f->proteins / 100);
            $fat += $foodstuff->amount * ($f->fats / 100);
            $ch += $foodstuff->amount * ($f->carbohydrates / 100);
            $foodstuff->foodstuff_category = FoodstuffCategory::where('id', $f->foodstuff_category_id)->get()[0]->name;
            $foodstuff->name = $f->name;
        }

        $recipe->calAmount = $cal;
        $recipe->proteinAmount = $prot;
        $recipe->fatsAmount = $fat;
        $recipe->chAmount = $ch;

        return $recipe;
    }

}
