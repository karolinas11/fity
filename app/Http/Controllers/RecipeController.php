<?php

namespace App\Http\Controllers;

use App\DataTables\RecipeDataTable;
use App\Models\Faq;
use App\Models\Foodstuff;
use App\Models\FoodstuffCategory;
use App\Models\Recipe;
use App\Models\User;
use App\Models\UserAllergy;
use App\Models\UserRecipe;
use App\Models\UserRecipeFoodstuff;
use App\Services\AuthService;
use App\Services\ImagesService;
use App\Services\RecipeFoodstuffService;
use App\Services\RecipeService;
use App\Services\UserRecipeService;
use App\Services\UserService;
use Carbon\Carbon;
use Dotenv\Parser\Parser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use function Pest\Laravel\json;

class RecipeController
{
    protected RecipeService $recipeService;
    protected RecipeFoodstuffService $recipeFoodstuffService;
    protected ImagesService $imagesService;
    protected UserRecipeService $userRecipeService;
    protected AuthService $authService;
    protected UserService $userService;

    public function __construct() {
        $this->recipeService = new RecipeService();
        $this->recipeFoodstuffService = new RecipeFoodstuffService();
        $this->imagesService = new ImagesService();
        $this->userRecipeService = new UserRecipeService();
        $this->authService = new AuthService();
        $this->userService = new UserService();
    }
    public function showAddRecipe() {
        $foodstuffs = Foodstuff::all();
        return view('create-recipe', compact('foodstuffs'));
    }

    public function addRecipe(Request $request) {

        $recipeData = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'short_description' => $request->input('short_description'),
            'type' => $request->input('type'),
            'insulin'=> $request->input('insulin'),
            'preparation_time' => $request->input('preparation_time'),
        ];

        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $imageName = $image->getClientOriginalName();
            $image->storeAs('public/featured_recipes', $imageName);
            $recipeData['featured_image'] = $imageName;
        }
        //dd($recipeData);
        $recipe = $this->recipeService->addRecipe($recipeData);

        $foodstuffs = json_decode($request->input('foodstuffs'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Nevalidan JSON za foodstuffs'], 400);
        }
        $this->recipeFoodstuffService->addRecipeFoodstuff($recipe->id, $foodstuffs);


        if ($request->hasFile('gallery_images')) {
            $galleryImages = $request->file('gallery_images');
            Log::info('Broj slika koje se šalju: ' . count($galleryImages));  // Ovo će prikazati broj slika koje Laravel prima
            foreach ($galleryImages as $image) {
                $imageName = $image->getClientOriginalName();
                $image->storeAs('public/gallery_recipes', $imageName);
                $this->imagesService->addImages($recipe->id, $imageName);
            }
        }
        return response()->json($recipe);
    }

    public function showRecipesList(RecipeDataTable $dataTable) {
        return $dataTable->render('recipes-list');
    }

    public function showRecipeEdit($id) {
        $recipe = Recipe::find($id);
        $foodstuffs = Foodstuff::all();
        $recipeFoodstuffs = $this->recipeFoodstuffService->getRecipeFoodstuffs($id);
        $fats = 0;
        $proteins = 0;
        $carbs = 0;
        foreach( $recipeFoodstuffs as $recipeFoodstuff){
            $foodstuff = Foodstuff::find($recipeFoodstuff->foodstuff_id);
            $proteins += ($recipeFoodstuff->amount / 100) * $foodstuff->proteins;
            $fats += ($recipeFoodstuff->amount / 100) * $foodstuff->fats;
            $carbs += ($recipeFoodstuff->amount / 100) * $foodstuff->carbohydrates;
        }

        $totalMass = $proteins + $fats + $carbs;

        $proteinPercentage = $totalMass > 0 ? ($proteins / $totalMass) * 100 : 0;
        $fatPercentage = $totalMass > 0 ? ($fats / $totalMass) * 100 : 0;
        $carbPercentage = $totalMass > 0 ? ($carbs / $totalMass) * 100 : 0;

        $totalCal = ($proteins * 4) + ($fats * 9) + ($carbs * 4);

        $proteinCalPercentage = $totalCal > 0 ? (($proteins * 4) / $totalCal) * 100 : 0;
        $fatCalPercentage = $totalCal > 0 ? (($fats * 9) / $totalCal) * 100 : 0;
        $carbCalPercentage = $totalCal > 0 ? (($carbs * 4) / $totalCal) * 100 : 0;

        return view('edit-recipe', compact('recipe', 'foodstuffs', 'recipeFoodstuffs','proteinPercentage', 'fatPercentage', 'carbPercentage', 'proteinCalPercentage', 'fatCalPercentage', 'carbCalPercentage'));
    }

    public function editRecipe(Request $request, $id) {
        $recipeData = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'short_description' => $request->input('short_description'),
            'insulin' => $request->input('insulin'),
            'type' => $request->input('type'),
            'preparation_time' => $request->input('preparation_time'),
        ];

        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $imageName = $image->getClientOriginalName();
            $image->storeAs('public/featured_recipes', $imageName);
            $recipeData['featured_image'] = $imageName;
        }

        //dd($request->all(), $request->file('featured_image'));


        $recipe = $this->recipeService->editRecipe($recipeData, $id);

        if ($request->hasFile('gallery_images')) {
            $this->imagesService->deleteRecipeImages($recipe->id);
            $galleryImages = $request->file('gallery_images');
            Log::info('Broj slika koje se šalju: ' . count($galleryImages));  // Ovo će prikazati broj slika koje Laravel prima
            foreach ($galleryImages as $image) {
                $imageName = $image->getClientOriginalName();
                $image->storeAs('public/gallery_recipes', $imageName);
                $this->imagesService->addImages($recipe->id, $imageName);
            }
        }

        $this->recipeFoodstuffService->deleteRecipeFoodstuff($recipe->id);
        $this->recipeFoodstuffService->addRecipeFoodstuff($recipe->id, json_decode($request->input('foodstuffs'), true));
        return redirect()->route('show-recipes-list');
    }

    public function deleteRecipe($id) {
        $recipe = Recipe::find($id);
        $recipe->delete();
        return redirect()->route('show-recipes-list');
    }

    public function testCurl() {
        $response = Http::timeout(240)->post('http://127.0.0.1:8000/meal-plan', [
            'target_calories' => 2405,
            'target_protein' => 141,
            'target_fat' => 84,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            echo json_encode($data);
        } else {
            $error = $response->body();
            echo $error;
        }
    }

    public function printFoodstuffs() {
        $foodstuffs = Foodstuff::all();
        foreach ($foodstuffs as $foodstuff) {
            $step = 1;
            if($foodstuff->name == 'Jaja') {
                $step = 50;
            } else if($foodstuff->name == 'Whey protein') {
                $step = 16;
            }
            if($foodstuff->min != null && $foodstuff->max != null) {
                echo $foodstuff->id . ',' . $foodstuff->calories . ',' . $foodstuff->proteins . ',' . $foodstuff->fats . ',' . $foodstuff->carbohydrates . ',' . $foodstuff->min . ',' . $foodstuff->max . ',' . $step . '<br/>';
            }
        }
    }

    public function printRecipes() {
        $recipes = Recipe::all();
        $recipesFinal = [];
        foreach ($recipes as $recipe) {
            $recipeFoodstuffs = $this->recipeFoodstuffService->getRecipeFoodstuffs($recipe->id);
            $tunaContain = false;
            $oatMeal = false;
            $eggs = false;
            $whey = 0;
            $hasFruit = false;
            $mealIngredients = '';

            if($recipe->id <= 160 && $recipe->id >= 139) {
                $hasFruit = true;
            }

            foreach ($recipeFoodstuffs as $recipeFoodstuff) {
                if($recipeFoodstuff->foodstuff_id == 17 ) {
                    $tunaContain = true;
                }
                if($recipeFoodstuff->foodstuff_id == 105 ) {
                    $whey = $recipeFoodstuff->amount;
                }
                if($recipeFoodstuff->foodstuff_id == 36 ) {
                    $oatMeal = true;
                }
                if($recipeFoodstuff->foodstuff_id == 9 || $recipeFoodstuff->foodstuff_id == 10) {
                    $eggs = true;
                }
            }

            $calories = 0;
            $proteins = 0;
            $fats = 0;
            $carbohydrates = 0;

            $holders = [];

            foreach ($recipeFoodstuffs as $recipeFoodstuff) {
                if ($recipeFoodstuff->proteins_holder == 0 && $recipeFoodstuff->fats_holder == 0 && $recipeFoodstuff->carbohydrates_holder == 0) {
                    $calories += Foodstuff::where('id', $recipeFoodstuff->foodstuff_id)->first()->calories * $recipeFoodstuff->amount / 100;
                    $proteins += Foodstuff::where('id', $recipeFoodstuff->foodstuff_id)->first()->proteins * $recipeFoodstuff->amount / 100;
                    $fats += Foodstuff::where('id', $recipeFoodstuff->foodstuff_id)->first()->fats * $recipeFoodstuff->amount / 100;
                    $carbohydrates += Foodstuff::where('id', $recipeFoodstuff->foodstuff_id)->first()->carbohydrates * $recipeFoodstuff->amount / 100;
                } else {
                    array_push($holders, $recipeFoodstuff);
                }

                $mealIngredients .= $recipeFoodstuff->foodstuff_id . '-';
            }

            $mealIngredients = substr($mealIngredients, 0, -1);

            $fixedCalories = $calories;
            $fixedProteins = $proteins;
            $fixedFats = $fats;
            $fixedCarbohydrates = $carbohydrates;

            $holdersMap = [
                'pHolder' => null,
                'fHolder' => null,
                'uHolder' => null
            ];
            foreach ($holders as $holder) {
                if($holder->proteins_holder != 0) {
                    $holdersMap['pHolder'] = $holder;
                }
                if($holder->fats_holder != 0) {
                    $holdersMap['fHolder'] = $holder;
                }
                if($holder->carbohydrates_holder != 0) {
                    $holdersMap['uHolder'] = $holder;
                }
            }

//            if($recipe->id == 10) {
//                dd($fMin);
//            }

            if($holdersMap['pHolder'] == null && $holdersMap['fHolder'] == null && $holdersMap['uHolder'] == null) {
                $name = $recipe->name . ' - Calories ' . $calories . ' - Proteins ' . $proteins . ' - Fats ' . $fats . ' - Carbohydrates ' . $carbohydrates;
                $recipeFinal = [
                    'id' => $recipe->id,
                    'category' => $recipe->type,
                    'calories_min' => $calories,
                    'proteins_min' => $proteins,
                    'fats_min' => $fats,
                    'carbohydrates_min' => $carbohydrates,
                    'calories_max' => $calories,
                    'proteins_max' => $proteins,
                    'fats_max' => $fats,
                    'carbohydrates_max' => $carbohydrates,
                    'tuna' => $tunaContain ? 1 : 0,
                    'whey' => $whey,
                    'holders' => '',
                    'fixedCalories' => $fixedCalories,
                    'fixedProteins' => $fixedProteins,
                    'fixedFats' => $fixedFats,
                    'fixedCarbohydrates' => $fixedCarbohydrates,
                    'eggBreakfast' => $eggs && $recipe->type == 1 ? 1 : 0,
                    'oatBreakfast' => $oatMeal && $recipe->type == 1 ? 1 : 0,
                    'hasFruit' => $hasFruit ? 1 : 0,
                    'mealIngredients' => $mealIngredients,
                ];
                array_push($recipesFinal, $recipeFinal);
            }

            if($holdersMap['pHolder'] != null && $holdersMap['fHolder'] == null && $holdersMap['uHolder'] == null) {
                $holder = $holdersMap['pHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $min = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $max = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $max - $min;

                $cal_min = $cal_max = $prot_min = $prot_max = $fat_min = $fat_max = $carb_min = $carb_max = 0;
                for($i = $min; $i <= $max; $i += $step) {
                    $prot = $proteins + ($i * $holderFoodstuff->proteins / 100);
                    $fat = $fats + ($i * $holderFoodstuff->fats / 100);
                    $carb = $carbohydrates + ($i * $holderFoodstuff->carbohydrates / 100);
                    $cal = $calories + ($i * $holderFoodstuff->calories / 100);
                    $name = $recipe->name . ' - Calories ' . $cal . ' - Proteins ' . $prot . ' - Fats ' . $fat . ' - Carbohydrates ' . $carb;
                    if($holder->foodstuff_id == 105) {
                        $whey = $i;
                    }

                    if($i == $min) {
                        $cal_min = $cal;
                        $prot_min = $prot;
                        $fat_min = $fat;
                        $carb_min = $carb;
                    }

                    if($i == $max) {
                        $cal_max = $cal;
                        $prot_max = $prot;
                        $fat_max = $fat;
                        $carb_max = $carb;
                    }
                }

                $recipeFinal = [
                    'id' => $recipe->id,
                    'category' => $recipe->type,
                    'calories_min' => $cal_min,
                    'proteins_min' => $prot_min,
                    'fats_min' => $fat_min,
                    'carbohydrates_min' => $carb_min,
                    'calories_max' => $cal_max,
                    'proteins_max' => $prot_max,
                    'fats_max' => $fat_max,
                    'carbohydrates_max' => $carb_max,
                    'tuna' => $tunaContain ? 1 : 0,
                    'whey' => $whey,
                    'holders' => $holder->foodstuff_id,
                    'fixedCalories' => $fixedCalories,
                    'fixedProteins' => $fixedProteins,
                    'fixedFats' => $fixedFats,
                    'fixedCarbohydrates' => $fixedCarbohydrates,
                    'eggBreakfast' => $eggs && $recipe->type == 1 ? 1 : 0,
                    'oatBreakfast' => $oatMeal && $recipe->type == 1 ? 1 : 0,
                    'hasFruit' => $hasFruit ? 1 : 0,
                    'mealIngredients' => $mealIngredients,
                ];

                array_push($recipesFinal, $recipeFinal);

            }

            if($holdersMap['pHolder'] != null && $holdersMap['fHolder'] != null && $holdersMap['uHolder'] == null) {
                $holder = $holdersMap['pHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $min = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $max = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $max - $min;

                $holder2 = $holdersMap['fHolder'];
                $holderFoodstuff2 = Foodstuff::find($holder2->foodstuff_id);
                $min2 = $holderFoodstuff2->min != null? $holderFoodstuff2->min : 100;
                $max2 = $holderFoodstuff2->max != null? $holderFoodstuff2->max : 500;
                $step2 = $max2 - $min2;

                $cal_min = $cal_max = $prot_min = $prot_max = $fat_min = $fat_max = $carb_min = $carb_max = 0;
                for($i = $min; $i <= $max; $i += $step) {
                    $prot = $proteins + ($i * $holderFoodstuff->proteins / 100);
                    $fat = $fats + ($i * $holderFoodstuff->fats / 100);
                    $carb = $carbohydrates + ($i * $holderFoodstuff->carbohydrates / 100);
                    $cal = $calories + ($i * $holderFoodstuff->calories / 100);
                    for($j = $min2; $j <= $max2; $j += $step2) {
                        $protF = $prot + ($j * $holderFoodstuff2->proteins / 100);
                        $fatF = $fat + ($j * $holderFoodstuff2->fats / 100);
                        $carbF = $carb + ($j * $holderFoodstuff2->carbohydrates / 100);
                        $calF = $cal + ($j * $holderFoodstuff2->calories / 100);
                        $name = $recipe->name . ' - Calories ' . $calF . ' - Proteins ' . $protF . ' - Fats ' . $fatF . ' - Carbohydrates ' . $carbF;
                        if($holder->foodstuff_id == 105) {
                            $whey = $i;
                        }
                        if($holder2->foodstuff_id == 105) {
                            $whey = $j;
                        }

                        if($i == $min && $j == $min2) {
                            $cal_min = $cal;
                            $prot_min = $prot;
                            $fat_min = $fat;
                            $carb_min = $carb;
                        } else if($i == $max && $j == $max2) {
                            $cal_max = $cal;
                            $prot_max = $prot;
                            $fat_max = $fat;
                            $carb_max = $carb;
                        }
                    }
                }

                $recipeFinal = [
                    'id' => $recipe->id,
                    'category' => $recipe->type,
                    'calories_min' => $cal_min,
                    'proteins_min' => $prot_min,
                    'fats_min' => $fat_min,
                    'carbohydrates_min' => $carb_min,
                    'calories_max' => $cal_max,
                    'proteins_max' => $prot_max,
                    'fats_max' => $fat_max,
                    'carbohydrates_max' => $carb_max,
                    'tuna' => $tunaContain ? 1 : 0,
                    'whey' => $whey,
                    'holders' => $holder->foodstuff_id . '-' . $holder2->foodstuff_id,
                    'fixedCalories' => $fixedCalories,
                    'fixedProteins' => $fixedProteins,
                    'fixedFats' => $fixedFats,
                    'fixedCarbohydrates' => $fixedCarbohydrates,
                    'eggBreakfast' => $eggs && $recipe->type == 1 ? 1 : 0,
                    'oatBreakfast' => $oatMeal && $recipe->type == 1 ? 1 : 0,
                    'hasFruit' => $hasFruit ? 1 : 0,
                    'mealIngredients' => $mealIngredients,
                ];

                array_push($recipesFinal, $recipeFinal);
            }

            if($holdersMap['pHolder'] != null && $holdersMap['fHolder'] != null && $holdersMap['uHolder'] != null) {
                $holder = $holdersMap['pHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $min = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $max = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $max - $min;

                $holder2 = $holdersMap['fHolder'];
                $holderFoodstuff2 = Foodstuff::find($holder2->foodstuff_id);
                $min2 = $holderFoodstuff2->min != null? $holderFoodstuff2->min : 100;
                $max2 = $holderFoodstuff2->max != null? $holderFoodstuff2->max : 500;
                $step2 = $max2 - $min2;

                $holder3 = $holdersMap['uHolder'];
                $holderFoodstuff3 = Foodstuff::find($holder3->foodstuff_id);
                $min3 = $holderFoodstuff3->min != null? $holderFoodstuff3->min : 100;
                $max3 = $holderFoodstuff3->max != null? $holderFoodstuff3->max : 500;
                $step3 = $max3 - $min3;

                $cal_min = $cal_max = $prot_min = $prot_max = $fat_min = $fat_max = $carb_min = $carb_max = 0;
                for($i = $min; $i <= $max; $i += $step) {
                    $prot = $proteins + ($i * $holderFoodstuff->proteins / 100);
                    $fat = $fats + ($i * $holderFoodstuff->fats / 100);
                    $carb = $carbohydrates + ($i * $holderFoodstuff->carbohydrates / 100);
                    $cal = $calories + ($i * $holderFoodstuff->calories / 100);

                    for($j = $min2; $j <= $max2; $j += $step2) {
                        $prot2 = ($j * $holderFoodstuff2->proteins / 100);
                        $fat2 = ($j * $holderFoodstuff2->fats / 100);
                        $carb2 = ($j * $holderFoodstuff2->carbohydrates / 100);
                        $cal2 = ($j * $holderFoodstuff2->calories / 100);

                        for($k = $min3; $k <= $max3; $k += $step3) {
                            $protF = $prot + $prot2 + ($k * $holderFoodstuff3->proteins / 100);
                            $fatF = $fat + $fat2 + ($k * $holderFoodstuff3->fats / 100);
                            $carbF = $carb + $carb2 + ($k * $holderFoodstuff3->carbohydrates / 100);
                            $calF = $cal + $cal2 + ($k * $holderFoodstuff3->calories / 100);
                            $name = $recipe->name . ' - Calories ' . $calF . ' - Proteins ' . $protF . ' - Fats ' . $fatF . ' - Carbohydrates ' . $carbF;
                            if($holder->foodstuff_id == 105) {
                                $whey = $i;
                            }
                            if($holder2->foodstuff_id == 105) {
                                $whey = $j;
                            }
                            if($holder3->foodstuff_id == 105) {
                                $whey = $k;
                            }

                            if($i == $min && $j == $min2 && $k == $min3) {
                                $cal_min = $calF;
                                $prot_min = $protF;
                                $fat_min = $fatF;
                                $carb_min = $carbF;
                            }

                            if($i == $max && $j == $max2 && $k == $max3) {
                                $cal_max = $calF;
                                $prot_max = $protF;
                                $fat_max = $fatF;
                                $carb_max = $carbF;
                            }
                        }
                    }
                }

                $recipeFinal = [
                    'id' => $recipe->id,
                    'category' => $recipe->type,
                    'calories_min' => $cal_min,
                    'proteins_min' => $prot_min,
                    'fats_min' => $fat_min,
                    'carbohydrates_min' => $carb_min,
                    'calories_max' => $cal_max,
                    'proteins_max' => $prot_max,
                    'fats_max' => $fat_max,
                    'carbohydrates_max' => $carb_max,
                    'tuna' => $tunaContain ? 1 : 0,
                    'whey' => $whey,
                    'holders' => $holder->foodstuff_id . '-' . $holder2->foodstuff_id . '-' . $holder3->foodstuff_id,
                    'fixedCalories' => $fixedCalories,
                    'fixedProteins' => $fixedProteins,
                    'fixedFats' => $fixedFats,
                    'fixedCarbohydrates' => $fixedCarbohydrates,
                    'eggBreakfast' => $eggs && $recipe->type == 1 ? 1 : 0,
                    'oatBreakfast' => $oatMeal && $recipe->type == 1 ? 1 : 0,
                    'hasFruit' => $hasFruit ? 1 : 0,
                    'mealIngredients' => $mealIngredients,
                ];

                array_push($recipesFinal, $recipeFinal);
            }

            if($holdersMap['pHolder'] == null && $holdersMap['fHolder'] != null && $holdersMap['uHolder'] == null) {
                $holder = $holdersMap['fHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $fMin = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $fMax = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $fMax - $fMin;

                $cal_min = $cal_max = $prot_min = $prot_max = $fat_min = $fat_max = $carb_min = $carb_max = 0;
                for($j = $fMin; $j <= $fMax; $j += $step) {
                    $fat = $fats + ($j * $holderFoodstuff->fats / 100);
                    $prot = $proteins + ($j * $holderFoodstuff->proteins / 100);
                    $carb = $carbohydrates + ($j * $holderFoodstuff->carbohydrates / 100);
                    $cal = $calories + ($j * $holderFoodstuff->calories / 100);
                    $name = $recipe->name . ' - Calories ' . $cal . ' - Proteins ' . $prot . ' - Fats ' . $fat . ' - Carbohydrates ' . $carb;
                    if($holder->foodstuff_id == 105) {
                        $whey = $j;
                    }

                    if($j == $fMin) {
                        $cal_min = $cal;
                        $prot_min = $prot;
                        $fat_min = $fat;
                        $carb_min = $carb;
                    }

                    if($j == $fMax) {
                        $cal_max = $cal;
                        $prot_max = $prot;
                        $fat_max = $fat;
                        $carb_max = $carb;
                    }
                }

                $recipeFinal = [
                    'id' => $recipe->id,
                    'category' => $recipe->type,
                    'calories_min' => $cal_min,
                    'proteins_min' => $prot_min,
                    'fats_min' => $fat_min,
                    'carbohydrates_min' => $carb_min,
                    'calories_max' => $cal_max,
                    'proteins_max' => $prot_max,
                    'fats_max' => $fat_max,
                    'carbohydrates_max' => $carb_max,
                    'tuna' => $tunaContain ? 1 : 0,
                    'whey' => $whey,
                    'holders' => $holder->foodstuff_id,
                    'fixedCalories' => $fixedCalories,
                    'fixedProteins' => $fixedProteins,
                    'fixedFats' => $fixedFats,
                    'fixedCarbohydrates' => $fixedCarbohydrates,
                    'eggBreakfast' => $eggs && $recipe->type == 1 ? 1 : 0,
                    'oatBreakfast' => $oatMeal && $recipe->type == 1 ? 1 : 0,
                    'hasFruit' => $hasFruit ? 1 : 0,
                    'mealIngredients' => $mealIngredients,
                ];

                array_push($recipesFinal, $recipeFinal);
            }

            if($holdersMap['pHolder'] == null && $holdersMap['fHolder'] != null && $holdersMap['uHolder'] != null) {
                $holder = $holdersMap['fHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $min = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $max = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $max - $min;

                $holder2 = $holdersMap['uHolder'];
                $holderFoodstuff2 = Foodstuff::find($holder2->foodstuff_id);
                $min2 = $holderFoodstuff2->min != null? $holderFoodstuff2->min : 100;
                $max2 = $holderFoodstuff2->max != null? $holderFoodstuff2->max : 500;
                $step2 = $max2 - $min2;

                $cal_min = $cal_max = $prot_min = $prot_max = $fat_min = $fat_max = $carb_min = $carb_max = 0;
                for($i = $min; $i <= $max; $i += $step) {
                    $prot = $proteins + ($i * $holderFoodstuff->proteins / 100);
                    $fat = $fats + ($i * $holderFoodstuff->fats / 100);
                    $carb = $carbohydrates + ($i * $holderFoodstuff->carbohydrates / 100);
                    $cal = $calories + ($i * $holderFoodstuff->calories / 100);
                    for($j = $min2; $j <= $max2; $j += $step2) {
                        $protF = $prot + ($j * $holderFoodstuff2->proteins / 100);
                        $fatF = $fat + ($j * $holderFoodstuff2->fats / 100);
                        $carbF = $carb + ($j * $holderFoodstuff2->carbohydrates / 100);
                        $calF = $cal + ($j * $holderFoodstuff2->calories / 100);
                        $name = $recipe->name . ' - Calories ' . $calF . ' - Proteins ' . $protF . ' - Fats ' . $fatF . ' - Carbohydrates ' . $carbF;
                        if($holder->foodstuff_id == 105) {
                            $whey = $i;
                        }
                        if($holder2->foodstuff_id == 105) {
                            $whey = $j;
                        }

                        if($i == $min && $j == $min2) {
                            $cal_min = $cal;
                            $prot_min = $prot;
                            $fat_min = $fat;
                            $carb_min = $carb;
                        }

                        if($i == $max && $j == $max2) {
                            $cal_max = $cal;
                            $prot_max = $prot;
                            $fat_max = $fat;
                            $carb_max = $carb;
                        }
                    }
                }

                $recipeFinal = [
                    'id' => $recipe->id,
                    'category' => $recipe->type,
                    'calories_min' => $cal_min,
                    'proteins_min' => $prot_min,
                    'fats_min' => $fat_min,
                    'carbohydrates_min' => $carb_min,
                    'calories_max' => $cal_max,
                    'proteins_max' => $prot_max,
                    'fats_max' => $fat_max,
                    'carbohydrates_max' => $carb_max,
                    'tuna' => $tunaContain ? 1 : 0,
                    'whey' => $whey,
                    'holders' => $holder->foodstuff_id . '-' . $holder2->foodstuff_id,
                    'fixedCalories' => $fixedCalories,
                    'fixedProteins' => $fixedProteins,
                    'fixedFats' => $fixedFats,
                    'fixedCarbohydrates' => $fixedCarbohydrates,
                    'eggBreakfast' => $eggs && $recipe->type == 1 ? 1 : 0,
                    'oatBreakfast' => $oatMeal && $recipe->type == 1 ? 1 : 0,
                    'hasFruit' => $hasFruit ? 1 : 0,
                    'mealIngredients' => $mealIngredients,
                ];

                array_push($recipesFinal, $recipeFinal);
            }

            if($holdersMap['pHolder'] == null && $holdersMap['fHolder'] == null && $holdersMap['uHolder'] != null) {
                $holder = $holdersMap['uHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $fMin = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $fMax = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $fMax - $fMin;

                $cal_min = $cal_max = $prot_min = $prot_max = $fat_min = $fat_max = $carb_min = $carb_max = 0;
                for($j = $fMin; $j <= $fMax; $j += $step) {
                    $fat = $fats + ($j * $holderFoodstuff->fats / 100);
                    $prot = $proteins + ($j * $holderFoodstuff->proteins / 100);
                    $carb = $carbohydrates + ($j * $holderFoodstuff->carbohydrates / 100);
                    $cal = $calories + ($j * $holderFoodstuff->calories / 100);
                    $name = $recipe->name . ' - Calories ' . $cal . ' - Proteins ' . $prot . ' - Fats ' . $fat . ' - Carbohydrates ' . $carb;
                    if($holder->foodstuff_id == 105) {
                        $whey = $j;
                    }

                    if($j == $fMax) {
                        $cal_max = $cal;
                        $prot_max = $prot;
                        $fat_max = $fat;
                        $carb_max = $carb;
                    }

                    if($j == $fMin) {
                        $cal_min = $cal;
                        $prot_min = $prot;
                        $fat_min = $fat;
                        $carb_min = $carb;
                    }
                }

                $recipeFinal = [
                    'id' => $recipe->id,
                    'category' => $recipe->type,
                    'calories_min' => $cal_min,
                    'proteins_min' => $prot_min,
                    'fats_min' => $fat_min,
                    'carbohydrates_min' => $carb_min,
                    'calories_max' => $cal_max,
                    'proteins_max' => $prot_max,
                    'fats_max' => $fat_max,
                    'carbohydrates_max' => $carb_max,
                    'tuna' => $tunaContain ? 1 : 0,
                    'whey' => $whey,
                    'holders' => $holder->foodstuff_id,
                    'fixedCalories' => $fixedCalories,
                    'fixedProteins' => $fixedProteins,
                    'fixedFats' => $fixedFats,
                    'fixedCarbohydrates' => $fixedCarbohydrates,
                    'eggBreakfast' => $eggs && $recipe->type == 1 ? 1 : 0,
                    'oatBreakfast' => $oatMeal && $recipe->type == 1 ? 1 : 0,
                    'hasFruit' => $hasFruit ? 1 : 0,
                    'mealIngredients' => $mealIngredients,
                ];

                array_push($recipesFinal, $recipeFinal);
            }

            if($holdersMap['pHolder'] != null && $holdersMap['fHolder'] == null && $holdersMap['uHolder'] != null) {
                $holder = $holdersMap['pHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $min = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $max = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $max - $min;

                $holder2 = $holdersMap['uHolder'];
                $holderFoodstuff2 = Foodstuff::find($holder2->foodstuff_id);
                $min2 = $holderFoodstuff2->min != null? $holderFoodstuff2->min : 100;
                $max2 = $holderFoodstuff2->max != null? $holderFoodstuff2->max : 500;
                $step2 = $max2 - $min2;

                $cal_min = $cal_max = $prot_min = $prot_max = $fat_min = $fat_max = $carb_min = $carb_max = 0;
                for($i = $min; $i <= $max; $i += $step) {
                    $prot = $proteins + ($i * $holderFoodstuff->proteins / 100);
                    $fat = $fats + ($i * $holderFoodstuff->fats / 100);
                    $carb = $carbohydrates + ($i * $holderFoodstuff->carbohydrates / 100);
                    $cal = $calories + ($i * $holderFoodstuff->calories / 100);
                    for($j = $min2; $j <= $max2; $j += $step2) {
                        $protF = $prot + ($j * $holderFoodstuff2->proteins / 100);
                        $fatF = $fat + ($j * $holderFoodstuff2->fats / 100);
                        $carbF = $carb + ($j * $holderFoodstuff2->carbohydrates / 100);
                        $calF = $cal + ($j * $holderFoodstuff2->calories / 100);
                        $name = $recipe->name . ' - Calories ' . $calF . ' - Proteins ' . $protF . ' - Fats ' . $fatF . ' - Carbohydrates ' . $carbF;
                        if($holder->foodstuff_id == 105) {
                            $whey = $i;
                        }
                        if($holder2->foodstuff_id == 105) {
                            $whey = $j;
                        }

                        if($i == $min && $j == $min2) {
                            $cal_min = $cal;
                            $prot_min = $prot;
                            $fat_min = $fat;
                            $carb_min = $carb;
                        }

                        if($i == $max && $j == $max2) {
                            $cal_max = $cal;
                            $prot_max = $prot;
                            $fat_max = $fat;
                            $carb_max = $carb;
                        }
                    }
                }

                $recipeFinal = [
                    'id' => $recipe->id,
                    'category' => $recipe->type,
                    'calories_min' => $cal_min,
                    'proteins_min' => $prot_min,
                    'fats_min' => $fat_min,
                    'carbohydrates_min' => $carb_min,
                    'calories_max' => $cal_max,
                    'proteins_max' => $prot_max,
                    'fats_max' => $fat_max,
                    'carbohydrates_max' => $carb_max,
                    'tuna' => $tunaContain ? 1 : 0,
                    'whey' => $whey,
                    'holders' => $holder->foodstuff_id . '-' . $holder2->foodstuff_id,
                    'fixedCalories' => $fixedCalories,
                    'fixedProteins' => $fixedProteins,
                    'fixedFats' => $fixedFats,
                    'fixedCarbohydrates' => $fixedCarbohydrates,
                    'eggBreakfast' => $eggs && $recipe->type == 1 ? 1 : 0,
                    'oatBreakfast' => $oatMeal && $recipe->type == 1 ? 1 : 0,
                    'hasFruit' => $hasFruit ? 1 : 0,
                    'mealIngredients' => $mealIngredients,
                ];

                 array_push($recipesFinal, $recipeFinal);
            }

        }

        //dd($recipesFinal);

        foreach ($recipesFinal as $key => $recipe) {
            echo $key . ',' . $recipe['id'] . ',' . $recipe['category'] . ',' . $recipe['calories_min'] . ',' . $recipe['proteins_min'] . ',' . $recipe['fats_min'] . ',' . $recipe['carbohydrates_min'] . ',' . $recipe['calories_max'] . ',' . $recipe['proteins_max'] . ',' . $recipe['fats_max'] . ',' . $recipe['carbohydrates_max'] . ',' . $recipe['tuna'] . ',' . $recipe['whey'] . ',' . $recipe['holders'] . ',' . $recipe['fixedCalories'] . ',' . $recipe['fixedProteins'] . ',' . $recipe['fixedFats'] . ',' . $recipe['fixedCarbohydrates'] . ',' . $recipe['eggBreakfast'] . ',' . $recipe['oatBreakfast'] . ',' . $recipe['hasFruit'] . ',' . $recipe['mealIngredients'] . "<br>";
        }
    }

    private function calculateCalories($proteins, $fats, $carbohydrates) {
        return $proteins * 4 + $fats * 9 + $carbohydrates * 4;
    }

    public function getRecipes(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $recipes = Recipe::all();

        foreach ($recipes as &$recipe) {
            $recipe->foodstuffs = $recipe->recipeFoodstuffs;
            $description = str_replace('\n', "\n", $recipe->description);
            $recipe->steps = preg_split('/\r\n|\r|\n/', $description);
            $recipe->steps = array_filter($recipe->steps, fn($step) => trim($step) !== '');
            $cal = 0;
            $prot = 0;
            $fat = 0;
            $ch = 0;
            foreach ($recipe->foodstuffs as &$foodstuff) {
                $f = Foodstuff::where('id', $foodstuff->foodstuff_id)->get()[0];
                $cal += $foodstuff->amount * ($f->calories / 100);
                $prot += $foodstuff->amount * ($f->proteins / 100);
                $fat += $foodstuff->amount * ($f->fats / 100);
                $ch += $foodstuff->amount * ($f->carbohydrates / 100);
                $foodstuff->foodstuff_category = FoodstuffCategory::where('id', $f->foodstuff_category_id)->get()[0]->name;
                $foodstuff->name = $f->name;
            }

            $recipe->calAmount = $cal;
            $recipe->proteinAmount = $prot;
            $recipe->fatsAmount = $fat;
            $recipe->chAmount = $ch;
            $recipe->image = asset('storage/featured_recipes/' . $recipe->featured_image);
        }

        return response()->json($recipes);
    }

    public function getGroceriesListForUser(Request $request) {
        $recipes = $this->userRecipeService->getUserRecipesByDate($request->userId, '1900-01-01', '2100-01-01');
        $foodstuffs = [];

        foreach ($recipes as $recipe) {
            foreach ($recipe->foodstuffs as &$foodstuff) {
                $foodstuff->category = FoodstuffCategory::where('id', $foodstuff->foodstuff->foodstuff_category_id)->get()[0]->name;
            }
        }
    }

    public function updateRecipeStatus(Request $request) {

        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $recipe = UserRecipe::find($request->recipeId);
        $recipe->status = $request->status;
        $recipe->skip_reason = $request->skipReason;
        $recipe->save();

        return response()->json($recipe);
    }

    public function updateRecipeBookmarkStatus(Request $request) {

        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();

        Log::error('REQUEST: ' . json_encode($request->all()));

        if($request->screen == 'planer') {
            $recipe = UserRecipe::find($request->recipeId);

            if($request->input('status') == 'bookmarked') {
                $recipe->bookmarked_status = 1;
            } else if ($request->input('status') == 'deleted') {
                $recipe->bookmarked_status = -1;
            } else {
                $recipe->bookmarked_status = 0;
            }
            $recipe->save();

            return response()->json($recipe);
        } else {
            $recipe = Recipe::find($request->recipeId);
            $userRecipes = UserRecipe::where('user_id', $user->id)
                ->where('recipe_id', $request->recipeId)
                ->get();
            foreach($userRecipes as $userRecipe) {
                if($request->input('status') == 'bookmarked') {
                    $userRecipe->bookmarked_status = 1;
                } else if ($request->input('status') == 'deleted') {
                    $userRecipe->bookmarked_status = -1;
                } else {
                    $userRecipe->bookmarked_status = 0;
                }
                $recipe->save();
            }

            return response()->json($recipe);
        }

    }


    public function getFaqs(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $faqs = Faq::where('category', '=', $request->category)->get();

        foreach ($faqs as &$faq) {
            $faq->html_url = 'https://fity.c-slatkatradicija.mystableserver.com/api/faq/' . $faq->id;
        }

        return response()->json($faqs);
    }

    public function getFaqCategories(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $categories = Faq::select('category')->distinct()->pluck('category')->toArray();
        return response()->json($categories);
    }

    public function getFaq($id) {
        $faq = Faq::find($id);

        return view('faq', compact('faq'));
    }

    public function filterRecipes(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::error('FILTER: ' . print_r($request->all(), true));

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();

        $recipes = Recipe::all();
        $types = (array) $request->input('types');
        $types = array_map(function ($t) {
            return (int) $t === 4 ? 2 : (int) $t;
        }, $types);
        $foodstuffs = (array) $request->input('foodstuffs');
        $recipesFinal = [];

        foreach ($recipes as &$recipe) {
            if(!empty($types)) {
                if(!in_array($recipe->type, $types)) {
                    continue;
                }
            }

            if(!empty($foodstuffs)) {
                $hasFoodstuff = false;
                foreach ($recipe->foodstuffs as $foodstuff) {
                    if(in_array($foodstuff->name, $foodstuffs)) {
                        $hasFoodstuff = true;
                    }
                }

                if(!$hasFoodstuff) {
                    continue;
                }
            }

            if($request->input('bookmarkStatus') == 'bookmarked') {
                $bookmarkedRecipe = UserRecipe::where('recipe_id', $recipe->id)
                    ->where('user_id', $user->id)
                    ->where('bookmarked_status', 1)
                    ->get()
                    ->first();

                if (!$bookmarkedRecipe) {
                    continue;
                }
            } else if($request->input('bookmarkStatus') == 'deleted') {
                $deletedRecipe = UserRecipe::where('recipe_id', $recipe->id)
                    ->where('user_id', $user->id)
                    ->where('bookmarked_status', -1)
                    ->get()
                    ->first();

                if (!$deletedRecipe) {
                    continue;
                }
            }

            $userRecipes = UserRecipe::where('recipe_id', $recipe->id)
                ->where('user_id', $user->id)
                ->get();

            $b = 'active';
            foreach($userRecipes as $userRecipe) {
                if($userRecipe->bookmarked_status == 1) {
                    $b = 'bookmarked';
                } else if($userRecipe->bookmarked_status == -1) {
                    $b = 'deleted';
                }
            }

            $recipe->bookmarked_status = $b;

            $recipesFinal[] = $recipe;
        }

        return response()->json($recipesFinal);
    }

    public function getFoodstuffsByCategory(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $category = FoodstuffCategory::where('name', '=', $request->category)->get()->first();

        $foodstuffs = Foodstuff::where('foodstuff_category_id', '=', $category->id)->get();
        foreach ($foodstuffs as &$foodstuff) {
            $foodstuff->foodstuff_category = $category->name;
        }
        return response()->json($foodstuffs);
    }

    public function generateNewPlan(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::error('GENERATE PLAN REQUEST: ' . print_r($request->all(), true));

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();
        $user->weight = $request->input('weight');
        $user->goal = $request->input('goal');
        $user->meals_num = $request->input('meals');
        $activity = 1.2;
        switch (strtolower($request->input('activity'))) {
            case 'nimalo aktivni':
                $activity = 1.2;
                break;
            case 'slabo aktivni':
                $activity = 1.375;
                break;
            case 'srednje aktivni':
                $activity = 1.55;
                break;
            case 'vrlo aktivni':
                $activity = 1.725;
                break;
            case 'ekstremno aktivni':
                $activity = 1.9;
                break;
        }
        $user->activity = $activity;
        $user->save();
        $foodstuffs = $request->removed_foodstuffs;
        $userAllergies = UserAllergy::where('user_id', $user->id)->get();
        foreach($userAllergies as $userAllergy) {
            $userAllergy->delete();
        }

        $allergyIds = [];
        foreach($foodstuffs as $foodstuff) {
            $f = Foodstuff::where('name', $foodstuff)->get()->first();
            $ua = UserAllergy::create(['user_id' => $user->id, 'foodstuff_id' => $f->id]);
            $allergyIds[] = $f->id;
        }

        $rawStart = $request->input('start_date');
        $startDate = Carbon::parse($rawStart)
            ->toDateString();
        $userRecipes = UserRecipe::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->get();
        foreach ($userRecipes as $ur) {
            $ur->delete();
        }

        $target = $this->userService->getMacrosForUser2($user);
        $response = Http::timeout(10000)
            ->withoutVerifying()
            ->post('https://fity-algorithm.fly.dev/meal-plan', [
                'target_calories' => $target['calories'],
                'target_protein' => $target['proteins'],
                'target_fat' => $target['fats'],
                'meals_num' => $user->meals_num,
                'tolerance_calories' => $user->tolerance_calories,
                'tolerance_proteins' => $user->tolerance_proteins,
                'tolerance_fats' => $user->tolerance_fats,
                'days' => $user->days,
                'allergy_holder_ids' => $allergyIds
            ]);


        $data = $response->json();

        $i = 0;
        for($k = 0; $k < 5; $k++) {
            foreach ($data['daily_plans'] as $day) {
                if(!$day['exists']) continue;
                $date = date(
                    'Y-m-d',
                    strtotime($startDate . " +{$i} days")
                );
                $i++;
                $lunch = false;
                foreach ($day['meals'] as $meal) {
//                if($meal['same_meal_id'] == 33) {
//                    continue;
//                }
                    $r = Recipe::find($meal['same_meal_id']);
                    $userRecipe = UserRecipe::create([
                        'user_id' => $user->id,
                        'recipe_id' => $meal['same_meal_id'],
                        'status' => 'active',
                        'date' => $date,
                        'type' => $lunch && $r->type == 2? 4: $r->type
                    ]);
                    if($r->type == 2) {
                        $lunch = true;
                    }
                    $foodstuffs = $this->recipeFoodstuffService->getRecipeFoodstuffs($meal['same_meal_id']);
                    foreach ($foodstuffs as $foodstuff) {
                        if($foodstuff->proteins_holder == 0 && $foodstuff->fats_holder == 0 && $foodstuff->carbohydrates_holder == 0) {
                            UserRecipeFoodstuff::create([
                                'user_recipe_id' => $userRecipe->id,
                                'foodstuff_id' => $foodstuff->foodstuff_id,
                                'amount' => $foodstuff->amount,
                                'purchased' => 0
                            ]);
                        }
                    }

                    foreach ($meal['holder_quantities'] as $key => $holder) {
                        UserRecipeFoodstuff::create([
                            'user_recipe_id' => $userRecipe->id,
                            'foodstuff_id' => $key,
                            'amount' => $holder,
                            'purchased' => 0
                        ]);
                    }
                }
            }
        }
    }
}
