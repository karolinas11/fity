<?php

namespace App\Services;

use App\Repositories\RecipeRepository;

class RecipeService
{
    protected RecipeRepository $recipeRepository;

    public function __construct() {
        $this->recipeRepository = new RecipeRepository();
    }
    public function addRecipe($recipeData) {
        return $this->recipeRepository->addRecipe($recipeData);
    }
}
