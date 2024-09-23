<?php

namespace App\Services;

use App\Repositories\FoodstuffCategoryRepository;

class FoodstuffCategoryService
{
    protected FoodstuffCategoryRepository $foodstuffCategoryRepository;

    public function __construct() {
        $this->foodstuffCategoryRepository = new FoodstuffCategoryRepository();
    }

    public function addFoodstuffCategory($foodstuffCategoryData) {
        return $this->foodstuffCategoryRepository->addFoodstuffCategory($foodstuffCategoryData);
    }
}
