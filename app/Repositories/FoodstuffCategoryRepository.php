<?php

namespace App\Repositories;

use App\Models\FoodstuffCategory;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;

class FoodstuffCategoryRepository
{
    public function addFoodstuffCategory($foodstuffCategoryData) {
        try {
            return FoodstuffCategory::create($foodstuffCategoryData);
        } catch (\Exception $e) {
            Log::error('Can\'t add foodstuff category: ' . $e->getMessage());
        }
    }
    public function getFoodstuffCategoriesAll(){
        try{
            return FoodstuffCategory::with('foodstuffsOption')->get();
        }catch (\Exception $e){
            Log::error('Can\'t fetch foodstuff category list: '.$e->getMessage());
        }
    }
}
