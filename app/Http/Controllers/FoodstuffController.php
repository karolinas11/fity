<?php

namespace App\Http\Controllers;

use App\DataTables\FoodstuffDataTable;
use App\Models\Foodstuff;
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
             'min' => $request->input('min'),
             'max' => $request->input('max'),
             'step' => $request->input('step'),
         ];

         $foodstuff = $this->foodstuffService->addFoodstuff($foodstuffData);
         return redirect()->route('show-add-foodstuff');
     }

    public function showFoodstuffsList(FoodstuffDataTable $dataTable) {
        return $dataTable->render('foodstuffs-list');
    }

    public function showFoodstuffEdit($id) {
        $foodstuff = Foodstuff::find($id);
        $foodstuffCategories = FoodstuffCategory::all();
        return view('edit-foodstuff', compact('foodstuff', 'foodstuffCategories'));
    }

    public function editFoodstuff(Request $request, $id) {
        $foodstuffData = [
            'name' => $request->input('name'),
            'foodstuff_category_id' => $request->input('foodstuff_category_id'),
            'amount' => $request->input('amount'),
            'measurement_unit' => $request->input('measurement_unit'),
            'calories' => $request->input('calories'),
            'proteins' => $request->input('proteins'),
            'fats' => $request->input('fats'),
            'carbohydrates' => $request->input('carbohydrates'),
            'min' => $request->input('min'),
            'max' => $request->input('max'),
            'step' => $request->input('step'),
        ];
        $foodstuff = $this->foodstuffService->editFoodstuff($foodstuffData, $id);
        return redirect()->route('show-foodstuffs-list');
    }


    public function deleteFoodstuff($id) {
        $foodstuff = Foodstuff::find($id);
        $foodstuff->delete();
        return redirect()->route('show-foodstuffs-list');
    }

}
