<?php

namespace App\Services;

use App\Repositories\FoodstuffRepository;

class FoodstuffService
{
    protected FoodstuffRepository $foodstuffRepository;

    public function __construct() {
        $this->foodstuffRepository = new FoodstuffRepository();
    }

    public function addFoodstuff($foodstuffdata) {
        return $this->foodstuffRepository->addFoodstuff($foodstuffdata);
    }
}
