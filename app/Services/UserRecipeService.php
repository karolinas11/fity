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

    public function getUserRecipes($userId, $startDate, $endDate) {
        return $this->userRecipeRepository->getUserRecipes($userId, $startDate, $endDate);
    }
}
