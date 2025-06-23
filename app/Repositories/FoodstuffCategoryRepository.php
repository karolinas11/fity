<?php

namespace App\Repositories;

use App\Models\Foodstuff;
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
            $categories = FoodstuffCategory::all();
            foreach ($categories as &$category){
                $category->foodstuffs = Foodstuff::where('foodstuff_category_id', $category->id)->get();
            }
            return $categories;
        }catch (\Exception $e){
            Log::error('Can\'t fetch foodstuff category list: '.$e->getMessage());
        }
    }
}
