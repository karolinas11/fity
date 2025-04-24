<?php

namespace App\Services;

use App\Http\Controllers\UserController;
use App\Repositories\UserRecipeRepository;

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
            $recipe->foodstuffs = $recipe->foodstuffs;
        }
        $recipesByDate = $recipes->groupBy(function ($recipe) {
            return \Carbon\Carbon::parse($recipe->date)->format('Y-m-d');
        });
        $recipesByDate = $recipesByDate->sortKeys();
        return $recipesByDate;
    }

}
