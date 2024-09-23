<?php

namespace App\Http\Controllers;

use App\Services\FoodstuffCategoryService;
use Illuminate\Http\Request;

class FoodstuffCategoryController extends Controller
{
    protected FoodstuffCategoryService $foodstuffCategoryService;

    public function __construct() {
        $this->foodstuffCategoryService = new FoodstuffCategoryService();
    }

    public function showAddFoodstuffCategory() {
        return view('create-foodstuff-category');
    }

    public function addFoodstuffCategory(Request $request) {
        $foodstuffCategoryData = [
            'name' => $request->input('name')
        ];

        $foodstuffCategory = $this->foodstuffCategoryService->addFoodstuffCategory($foodstuffCategoryData);
        return response()->json($foodstuffCategory);
    }

}
