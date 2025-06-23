<?php

namespace App\Http\Controllers;

use App\DataTables\FoodstuffDataTable;
use App\Models\Foodstuff;
use App\Models\FoodstuffCategory;
use App\Services\AuthService;
use App\Services\FoodstuffService;
use App\Services\FoodstuffCategoryService;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class FoodstuffController extends Controller
{
     protected FoodstuffService $foodstuffService;
     protected FoodstuffCategoryService $foodstuffCategoryService;
     protected AuthService $authService;

     public function __construct() {
         $this->foodstuffService = new FoodstuffService();
         $this->foodstuffCategoryService = new FoodstuffCategoryService();
         $this->authService = new AuthService();
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

         if ($request->hasFile('featured_image')) {
             $image = $request->file('featured_image');
             $imageName = $image->getClientOriginalName();
             $image->storeAs('public/featured_foodstuffs', $imageName);
             $foodstuffData['featured_image'] = $imageName;
         }

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

        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $imageName = $image->getClientOriginalName();
            $image->storeAs('public/featured_foodstuffs', $imageName);
            $foodstuffData['featured_image'] = $imageName;
        }

        $foodstuff = $this->foodstuffService->editFoodstuff($foodstuffData, $id);
        return redirect()->route('show-foodstuffs-list');
    }


    public function deleteFoodstuff($id) {
        $foodstuff = Foodstuff::find($id);
        $foodstuff->delete();
        return redirect()->route('show-foodstuffs-list');
    }

    public function foodstuffCategories(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json($this->foodstuffCategoryService->getFoodstuffCategories());
    }

}
