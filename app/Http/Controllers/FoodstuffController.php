<?php

namespace App\Http\Controllers;

use App\Models\FoodstuffCategory;
use App\Services\FoodstuffService;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class FoodstuffController extends Controller
{
 protected FoodstuffService $foodstuffService;

 public function __construct() {
     $this->foodstuffService = new FoodstuffService();
 }

 public function showAddFoodstuff() {
     $foodstuffCategories = FoodstuffCategory::all();
     return view('create-foodstuff', compact('foodstuffCategories'));
 }

 public function addFoodstuff(Request $request) {
     $foodstuffData = [
         'name' => $request->input('name'),
         'foodstuff_category_id' => $request->input('foodstuff_category_id'),
         'amount' => $request->input('amount'),
         'measurement_unit' => $request->input('measurement_unit'),
         'calories' => $request->input('calories'),
         'proteins' => $request->input('proteins'),
         'fats' => $request->input('fats'),
         'carbohydrates' => $request->input('carbohydrates'),
     ];

     $foodstuff = $this->foodstuffService->addFoodstuff($foodstuffData);
     return response()->json($foodstuff);
 }


}
