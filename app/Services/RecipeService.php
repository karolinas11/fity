<?php

namespace App\Services;

use App\Models\Foodstuff;
use App\Models\Recipe;
use App\Models\RecipeFoodstuff;
use App\Repositories\RecipeRepository;

class RecipeService
{
    protected RecipeRepository $recipeRepository;

    public function __construct() {
        $this->recipeRepository = new RecipeRepository();
    }
    public function addRecipe($recipeData) {
        return $this->recipeRepository->addRecipe($recipeData);
    }

    public function editRecipe($recipeData, $id) {
        return $this->recipeRepository->editRecipe($recipeData, $id);
    }

    public function getRecipeFoodstuffs($id) {
        return $this->recipeRepository->getRecipeFoodstuffs($id);
    }

    public function getRecipeAlternatives($userRecipe) {
        $cal = 0;
        $prot = 0;
        $fat = 0;
        //$ch = 0;
        foreach ($userRecipe->foodstuffs as &$foodstuff) {
            $f = Foodstuff::where('id', $foodstuff->foodstuff_id)->get()[0];
            $cal += $foodstuff->amount * ($f->calories / 100);
            $prot += $foodstuff->amount * ($f->proteins / 100);
            $fat += $foodstuff->amount * ($f->fats / 100);
            //$ch += $foodstuff->amount * ($f->carbohydrates / 100);
        }

        $type = $userRecipe->type == 4 ? 2: $userRecipe->type;
        $recipes = Recipe::where('type', $type)->get();
        $combinations = [];

        foreach ($recipes as $recipe) {
            $rCal = 0;
            $rProt = 0;
            $rFat = 0;
            //$rCh = 0;

            $holders = [];
            foreach ($recipe->foodstuffs as $fm) {
                $f = RecipeFoodstuff::where('foodstuff_id', $fm->id)
                    ->where('recipe_id', $recipe->id)
                    ->get()[0];
                if($f->proteins_holder == 0 && $f->fats_holder == 0 && $f->carbohydrates_holder == 0) {
                    $rCal += $f->amount * ($fm->calories / 100);
                    $rProt += $f->amount * ($fm->proteins / 100);
                    $rFat += $f->amount * ($fm->fats / 100);
                    //$rCh += $f->amount * ($fm->carbohydrates / 100);
                } else {
                    $holders[] = $fm;
                }
            }

            if(count($holders) > 0) {
                $rCalMin = $rProtMin = $rFatMin = $rChMin = 0;
                $rCalMax = $rProtMax = $rFatMax = $rChMax = 0;

                foreach ($holders as $h) {
                    $rCalMin += $h->min * ($h->calories      / 100);
                    $rProtMin += $h->min * ($h->proteins      / 100);
                    $rFatMin += $h->min * ($h->fats          / 100);
                    //$rChMin  += $h->min * ($h->carbohydrates / 100);

                    $rCalMax += $h->max * ($h->calories      / 100);
                    $rProtMax += $h->max * ($h->proteins      / 100);
                    $rFatMax += $h->max * ($h->fats          / 100);
                    //$rChMax  += $h->max * ($h->carbohydrates / 100);
                }

                $combinations[] = [
                    'caloriesMin'      => $rCal   + $rCalMin,
                    'proteinsMin'      => $rProt  + $rProtMin,
                    'fatsMin'          => $rFat   + $rFatMin,
                    //'carbohydratesMin' => $rCh    + $rChMin,
                    'caloriesMax'      => $rCal   + $rCalMax,
                    'proteinsMax'      => $rProt  + $rProtMax,
                    'fatsMax'          => $rFat   + $rFatMax,
                    //'carbohydratesMax' => $rCh    + $rChMax,
                    'recipe'           => $recipe->id,
                ];
            } else {
                $combinations[] = [
                    'caloriesMin' => $rCal,
                    'proteinsMin' => $rProt,
                    'fatsMin' => $rFat,
                    //'carbohydratesMin' => $rCh,
                    'caloriesMax' => $rCal,
                    'proteinsMax' => $rProt,
                    'fatsMax' => $rFat,
                    //'carbohydratesMax' => $rCh,
                    'recipe' => $recipe->id
                ];
            }
        }

        $usefullCombinations = [];

        foreach($combinations as $combination) {
            if($combination['caloriesMin'] <= $cal && $combination['caloriesMax'] >= $cal
                && $combination['proteinsMin'] <= $prot && $combination['proteinsMax'] >= $prot
                && $combination['fatsMin'] <= $fat && $combination['fatsMax'] >= $fat
                //&& $combination['carbohydratesMin'] <= $ch && $combination['carbohydratesMax'] >= $ch
                && $combination['recipe'] != $userRecipe->recipe_id) {
                $usefullCombinations[] = $combination;
            }
        }

        return [
            'combinations' => $usefullCombinations,
            'cal' => $cal,
            'prot' => $prot,
            'fat' => $fat,
            //'ch' => $ch
        ];
    }
}
