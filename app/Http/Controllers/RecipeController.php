<?php

namespace App\Http\Controllers;

use App\DataTables\RecipeDataTable;
use App\DataTables\RecipesDataTable;
use App\Models\Foodstuff;
use App\Models\Recipe;
use App\Services\RecipeFoodstuffService;
use App\Services\RecipeService;
use Dotenv\Parser\Parser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RecipeController
{
    protected RecipeService $recipeService;
    protected RecipeFoodstuffService $recipeFoodstuffService;


    public function __construct() {
        $this->recipeService = new RecipeService();
        $this->recipeFoodstuffService = new RecipeFoodstuffService();

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
            'insulin'=> $request->input('insulin')
        ];
        $recipe = $this->recipeService->addRecipe($recipeData);
        $this->recipeFoodstuffService->addRecipeFoodstuff($recipe->id, $request->input('foodstuffs'));
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

        return view('edit-recipe', compact('recipe', 'foodstuffs', 'recipeFoodstuffs','proteinPercentage', 'fatPercentage', 'carbPercentage'));
    }

    public function editRecipe(Request $request, $id) {
        $recipeData = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'short_description' => $request->input('short_description'),
            'insulin' => $request->input('insulin'),
            'type' => $request->input('type')
        ];
        $recipe = $this->recipeService->editRecipe($recipeData, $id);
        $this->recipeFoodstuffService->deleteRecipeFoodstuff($recipe->id);
        $this->recipeFoodstuffService->addRecipeFoodstuff($recipe->id, $request->input('foodstuffs'));
        return redirect()->route('show-recipes-list');
    }

    public function deleteRecipe($id) {
        $recipe = Recipe::find($id);
        $recipe->delete();
        return redirect()->route('show-recipes-list');
    }

    public function testCurl() {
        $response = Http::timeout(240)->post('https://fity-algorithm.fly.dev//meal-plan', [
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

    public function printRecipes() {
        $recipes = Recipe::all();
        $recipesFinal = [];
        foreach ($recipes as $recipe) {
            $recipeFoodstuffs = $this->recipeFoodstuffService->getRecipeFoodstuffs($recipe->id);
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
            }

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
                    'name' => $name,
                    'category' => $recipe->type,
                    'calories' => $calories,
                    'proteins' => $proteins,
                    'fats' => $fats,
                    'carbohydrates' => $carbohydrates,
                    'holders' => ''
                ];
                array_push($recipesFinal, $recipeFinal);
            }

            if($holdersMap['pHolder'] != null && $holdersMap['fHolder'] == null && $holdersMap['uHolder'] == null) {
                $holder = $holdersMap['pHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $min = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $max = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $holderFoodstuff->step != null? $holderFoodstuff->step : 25;
                for($i = $min; $i <= $max; $i += $step) {
                    $prot = $proteins + ($i * $holderFoodstuff->proteins / 100);
                    $fat = $fats + ($i * $holderFoodstuff->fats / 100);
                    $carb = $carbohydrates + ($i * $holderFoodstuff->carbohydrates / 100);
                    $cal = $calories + ($i * $holderFoodstuff->calories / 100);
                    $name = $recipe->name . ' - Calories ' . $cal . ' - Proteins ' . $prot . ' - Fats ' . $fat . ' - Carbohydrates ' . $carb;
                    $recipeFinal = [
                        'id' => $recipe->id,
                        'name' => $name,
                        'category' => $recipe->type,
                        'calories' => $cal,
                        'proteins' => $prot,
                        'fats' => $fat,
                        'carbohydrates' => $carb,
                        'holders' => $holder->foodstuff_id . ' - ' . $i
                    ];
                    array_push($recipesFinal, $recipeFinal);
                }
            }

            if($holdersMap['pHolder'] != null && $holdersMap['fHolder'] != null && $holdersMap['uHolder'] == null) {
                $holder = $holdersMap['pHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $min = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $max = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $holderFoodstuff->step != null? $holderFoodstuff->step : 25;

                $holder2 = $holdersMap['fHolder'];
                $holderFoodstuff2 = Foodstuff::find($holder2->foodstuff_id);
                $min2 = $holderFoodstuff2->min != null? $holderFoodstuff2->min : 100;
                $max2 = $holderFoodstuff2->max != null? $holderFoodstuff2->max : 500;
                $step2 = $holderFoodstuff2->step != null? $holderFoodstuff2->step : 25;

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
                        $recipeFinal = [
                            'id' => $recipe->id,
                            'name' => $name,
                            'category' => $recipe->type,
                            'calories' => $calF,
                            'proteins' => $protF,
                            'fats' => $fatF,
                            'carbohydrates' => $carbF,
                            'holders' => $holder->foodstuff_id . ' - ' . $i . ' | ' . $holder2->foodstuff_id . ' - ' . $j
                        ];
                        array_push($recipesFinal, $recipeFinal);
                    }
                }
            }

            if($holdersMap['pHolder'] != null && $holdersMap['fHolder'] != null && $holdersMap['uHolder'] != null) {
                $holder = $holdersMap['pHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $min = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $max = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $holderFoodstuff->step != null? $holderFoodstuff->step : 25;

                $holder2 = $holdersMap['fHolder'];
                $holderFoodstuff2 = Foodstuff::find($holder2->foodstuff_id);
                $min2 = $holderFoodstuff2->min != null? $holderFoodstuff2->min : 100;
                $max2 = $holderFoodstuff2->max != null? $holderFoodstuff2->max : 500;
                $step2 = $holderFoodstuff2->step != null? $holderFoodstuff2->step : 25;

                $holder3 = $holdersMap['uHolder'];
                $holderFoodstuff3 = Foodstuff::find($holder3->foodstuff_id);
                $min3 = $holderFoodstuff3->min != null? $holderFoodstuff3->min : 100;
                $max3 = $holderFoodstuff3->max != null? $holderFoodstuff3->max : 500;
                $step3 = $holderFoodstuff3->step != null? $holderFoodstuff3->step : 25;

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

                        for($k = $min3 + 25; $k <= $max3; $k += $step3) {
                            $protF = $prot + $prot2 + ($k * $holderFoodstuff3->proteins / 100);
                            $fatF = $fat + $fat2 + ($k * $holderFoodstuff3->fats / 100);
                            $carbF = $carb + $carb2 + ($k * $holderFoodstuff3->carbohydrates / 100);
                            $calF = $cal + $cal2 + ($k * $holderFoodstuff3->calories / 100);
                            $name = $recipe->name . ' - Calories ' . $calF . ' - Proteins ' . $protF . ' - Fats ' . $fatF . ' - Carbohydrates ' . $carbF;
                            $recipeFinal = [
                                'id' => $recipe->id,
                                'name' => $name,
                                'category' => $recipe->type,
                                'calories' => $calF,
                                'proteins' => $protF,
                                'fats' => $fatF,
                                'carbohydrates' => $carbF,
                                'holders' => $holder->foodstuff_id . ' - ' . $i . ' | ' . $holder2->foodstuff_id . ' - ' . $j . ' | ' . $holder3->foodstuff_id . ' - ' . $k
                            ];
                            array_push($recipesFinal, $recipeFinal);
                        }
                    }
                }
            }

            if($holdersMap['pHolder'] == null && $holdersMap['fHolder'] != null && $holdersMap['uHolder'] == null) {
                $holder = $holdersMap['fHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $fMin = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $fMax = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $holderFoodstuff->step != null? $holderFoodstuff->step : 25;
                for($j = $fMin; $j <= $fMax; $j += $step) {
                    $fat = $fats + ($j * $holderFoodstuff->fats / 100);
                    $prot = $proteins + ($j * $holderFoodstuff->proteins / 100);
                    $carb = $carbohydrates + ($j * $holderFoodstuff->carbohydrates / 100);
                    $cal = $calories + ($j * $holderFoodstuff->calories / 100);
                    $name = $recipe->name . ' - Calories ' . $cal . ' - Proteins ' . $prot . ' - Fats ' . $fat . ' - Carbohydrates ' . $carb;
                    $recipeFinal = [
                        'id' => $recipe->id,
                        'name' => $name,
                        'category' => $recipe->type,
                        'calories' => $cal,
                        'proteins' => $prot,
                        'fats' => $fat,
                        'carbohydrates' => $carb,
                        'holders' => $holder->foodstuff_id . ' - ' . $j
                    ];
                    array_push($recipesFinal, $recipeFinal);
                }
            }

            if($holdersMap['pHolder'] == null && $holdersMap['fHolder'] != null && $holdersMap['uHolder'] != null) {
                $holder = $holdersMap['fHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $min = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $max = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $holderFoodstuff->step != null? $holderFoodstuff->step : 25;

                $holder2 = $holdersMap['uHolder'];
                $holderFoodstuff2 = Foodstuff::find($holder2->foodstuff_id);
                $min2 = $holderFoodstuff2->min != null? $holderFoodstuff2->min : 100;
                $max2 = $holderFoodstuff2->max != null? $holderFoodstuff2->max : 500;
                $step = $holderFoodstuff2->step != null? $holderFoodstuff2->step : 25;

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
                        $recipeFinal = [
                            'id' => $recipe->id,
                            'name' => $name,
                            'category' => $recipe->type,
                            'calories' => $calF,
                            'proteins' => $protF,
                            'fats' => $fatF,
                            'carbohydrates' => $carbF,
                            'holders' => $holder->foodstuff_id . ' - ' . $i . ' | ' . $holder2->foodstuff_id . ' - ' . $j
                        ];
                        array_push($recipesFinal, $recipeFinal);
                    }
                }
            }

            if($holdersMap['pHolder'] == null && $holdersMap['fHolder'] == null && $holdersMap['uHolder'] != null) {
                $holder = $holdersMap['uHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $fMin = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $fMax = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $holderFoodstuff->step != null? $holderFoodstuff->step : 25;
                for($j = $fMin; $j <= $fMax; $j += $step) {
                    $fat = $fats + ($j * $holderFoodstuff->fats / 100);
                    $prot = $proteins + ($j * $holderFoodstuff->proteins / 100);
                    $carb = $carbohydrates + ($j * $holderFoodstuff->carbohydrates / 100);
                    $cal = $calories + ($j * $holderFoodstuff->calories / 100);
                    $name = $recipe->name . ' - Calories ' . $cal . ' - Proteins ' . $prot . ' - Fats ' . $fat . ' - Carbohydrates ' . $carb;
                    $recipeFinal = [
                        'id' => $recipe->id,
                        'name' => $name,
                        'category' => $recipe->type,
                        'calories' => $cal,
                        'proteins' => $prot,
                        'fats' => $fat,
                        'carbohydrates' => $carb,
                        'holders' => $holder->foodstuff_id . ' - ' . $j
                    ];
                    array_push($recipesFinal, $recipeFinal);
                }
            }

            if($holdersMap['pHolder'] != null && $holdersMap['fHolder'] == null && $holdersMap['uHolder'] != null) {
                $holder = $holdersMap['pHolder'];
                $holderFoodstuff = Foodstuff::find($holder->foodstuff_id);
                $min = $holderFoodstuff->min != null? $holderFoodstuff->min : 100;
                $max = $holderFoodstuff->max != null? $holderFoodstuff->max : 500;
                $step = $holderFoodstuff->step != null? $holderFoodstuff->step : 25;

                $holder2 = $holdersMap['uHolder'];
                $holderFoodstuff2 = Foodstuff::find($holder2->foodstuff_id);
                $min2 = $holderFoodstuff2->min != null? $holderFoodstuff2->min : 100;
                $max2 = $holderFoodstuff2->max != null? $holderFoodstuff2->max : 500;
                $step2 = $holderFoodstuff2->step != null? $holderFoodstuff2->step : 25;

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
                        $recipeFinal = [
                            'id' => $recipe->id,
                            'name' => $name,
                            'category' => $recipe->type,
                            'calories' => $calF,
                            'proteins' => $protF,
                            'fats' => $fatF,
                            'carbohydrates' => $carbF,
                            'holders' => $holder->foodstuff_id . ' - ' . $i . ' | ' . $holder2->foodstuff_id . ' - ' . $j
                        ];
                        array_push($recipesFinal, $recipeFinal);
                    }
                }
            }

        }


        foreach ($recipesFinal as $key => $recipe) {
            echo $key . ',' . $recipe['id'] . ',' . $recipe['name'] . ',' . $recipe['category'] . ',' . $recipe['calories'] . ',' . $recipe['proteins'] . ',' . $recipe['fats'] . ',' . $recipe['carbohydrates'] . ',' . $recipe['holders'] ."<br>";
        }
    }

    private function calculateCalories($proteins, $fats, $carbohydrates) {
        return $proteins * 4 + $fats * 9 + $carbohydrates * 4;
    }
}
