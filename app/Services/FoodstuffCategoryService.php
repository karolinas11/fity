<?php

namespace App\Services;

use App\Repositories\FoodstuffCategoryRepository;

class FoodstuffCategoryService
{
    protected FoodstuffCategoryRepository $foodstuffCategoryRepository;

    public function __construct() {
        $this->foodstuffCategoryRepository = new FoodstuffCategoryRepository();
    }
    public function getFoodstuffCategories(){
        return $this->foodstuffCategoryRepository->getFoodstuffCategoriesAll();
    }
    public function addFoodstuffCategory($foodstuffCategoryData) {
        return $this->foodstuffCategoryRepository->addFoodstuffCategory($foodstuffCategoryData);
    }

}
