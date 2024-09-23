<?php

namespace App\Repositories;

use App\Models\FoodstuffCategory;
use Illuminate\Support\Facades\Log;

class FoodstuffCategoryRepository
{
    public function addFoodstuffCategory($foodstuffCategoryData) {
        try {
            return FoodstuffCategory::create($foodstuffCategoryData);
        } catch (\Exception $e) {
            Log::error('Can\'t add foodstuff category: ' . $e->getMessage());
        }
    }
}
