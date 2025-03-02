<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Models\Foodstuff;
use App\Models\Recipe;
use App\Models\RecipeFoodstuff;
use App\Models\User;
use App\Services\RecipeFoodstuffService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UserController extends Controller
{
    protected UserService $userService;
    protected RecipeFoodstuffService $recipefoodstuffService;

    protected $bestCombination;
    protected $bestDifference;

    public function __construct() {
        $this->userService = new UserService();
        $this->recipefoodstuffService= new RecipeFoodstuffService();
        $this->bestCombination = [];
        $this->bestDifference = PHP_INT_MAX;
    }

    public function showAddUser()
    {
        return view('create-user');
    }

    //vratis iz funkcije macrose za tog usera, editujem  jedna koja edituje podatke druga koja vraca macrose, tu koje edituje podatke nju napravis
    public function editUser(Request $request){

        $userData = [
            'goal' => $request->input('goal'),
            'height' => $request->input('height'),
            'weight' => $request->input('weight'),
            'age' => $request->input('age'),
            'gender' => $request->input('gender'),
            'activity' => $request->input('activity'),
            'tolerance_proteins'=>$request->input('tolerance_proteins'),
            'tolerance_fats'=>$request->input('tolerance_fats'),
            'tolerance_calories'=>$request->input('tolerance_calories'),
            'meals_num'=>$request->input('meals_num'),
            'days'=>$request->input('days')
        ];

        $userId= $request->input('user_id');
        $user = $this->userService->editUser($userData, $userId);

        /* $target = $this->userService->getMacrosForUser($user);*/

        if(!$user){

            return response()->json(['success' => false, 'message'=> 'Korisnik nije pronadjen!'], 200);
        }
        $target = $this->userService->getMacrosForUser($user);
        return response()->json(['success'=> true, 'target'=> $target], 200);
    }
    public function addUser(Request $request) {
        $userData = [
            'goal' => $request->input('goal'),
            'height' => $request->input('height'),
            'weight' => $request->input('weight'),
            'age' => $request->input('age'),
            'gender' => $request->input('gender'),
            'activity' => $request->input('activity'),
            'insulin_resistance' => $request->input('insulin_resistance'),
            'meals_num' => $request->input('meals_num'),
            'tolerance_calories' => $request->input('tolerance_calories'),
            'tolerance_proteins' => $request->input('tolerance_proteins'),
            'tolerance_fats' => $request->input('tolerance_fats'),
            'days' => $request->input('days'),
        ];

        $user = $this->userService->addUser($userData);
        //dd($user);
        return redirect()->route('assign-recipes-to-user', ['userId' => $user->id]);
    }

    public function assignRecipesToUser($userId) {

        $user = User::find($userId);
        $target = $this->userService->getMacrosForUser($user);
        //dd($target);
        $response = Http::timeout(10000)->post('https://fity-algorithm.fly.dev/meal-plan', [
//        $response = Http::timeout(100000)->post('http://127.0.0.1:8000/meal-plan', [
            'target_calories' => $target['calories'],
            'target_protein' => $target['proteins'],
            'target_fat' => $target['fats'],
            'meals_num' => $user->meals_num,
            'tolerance_calories' => $user->tolerance_calories,
            'tolerance_proteins' => $user->tolerance_proteins,
            'tolerance_fats' => $user->tolerance_fats,
            'days' => $user->days
        ]);

        $data = $response->json();
        //dd($data);
        $combs = [];
        foreach($data['daily_plans'] as &$day){
            foreach( $day['meals'] as &$meal){
                $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($meal['same_meal_id']);
                $meal['foodstuffs'] = $foodstuffs;
                //                $foodstuffsData = [];
//                $input = $meal['holders'];
//                $pairs = explode(' | ', $input);
//                $holders = [];
//                foreach ($pairs as $pair) {
//                    list($key, $value) = explode(' - ', $pair);
//                    /* $holders[(int)$key] = (int)$value;*/
//                    $holderFoodStuffRecipe =RecipeFoodstuff::where('foodstuff_id', (int)$key)
//                        ->where('recipe_id',$meal['same_meal_id'])->first();
//
//                    if($holderFoodStuffRecipe)
//                    {
//                        $singleHolder = [
//                            'id' => (int)$key,
//                            'name' =>Foodstuff::find((int)$key)->name,
//                            'amount' => (int)$value,
//                            'p' => $holderFoodStuffRecipe->proteins_holder,
//                            'f' => $holderFoodStuffRecipe->fats_holder,
//                            'c' => $holderFoodStuffRecipe->carbohydrates_holder
//                        ];
//
//                        array_push($holders,$singleHolder);
//                    }
//                }
//
//                foreach ($foodstuffs as $foodstuff){
//                    if($foodstuff->proteins_holder == 0 && $foodstuff->fats_holder == 0 && $foodstuff->carbohydrates_holder == 0) {
//                        $foodstuffData = [];
//                        $foodstuffData['amount'] = $foodstuff['amount'];
//                        $foodstuffData['name'] = Foodstuff::find($foodstuff->foodstuff_id)->name;
//                        array_push($foodstuffsData,$foodstuffData);
//                    }
//
//                }
//                $meal['foodstuffs'] = $foodstuffsData;
//                $meal['holders'] = $holders;
            }
        }
        //dd($data);

        return view('user-recipes', compact('user', 'target', 'data'));
    }

    private function findBestCombination($ingredients, $targets, $selected = [], $index = 0) {

        // Base case: all ingredients are selected
        if ($index === count($ingredients)) {
            $total = ['calories' => 0, 'proteins' => 0, 'fats' => 0];

            // Sum selected ingredient values
            foreach ($selected as $variant) {
                if (is_array($variant) && isset($variant['calories'], $variant['proteins'], $variant['fats'])) {
                    $total['calories'] += $variant['calories'];
                    $total['proteins'] += $variant['proteins'];
                    $total['fats'] += $variant['fats'];
                } else {
                    var_dump($variant); // Debugging
                    exit("Error: Variant is not valid.");
                }

            }

            // Compute difference from target
            $difference = abs($total['calories'] - $targets['calories']) +
                abs($total['proteins'] - $targets['proteins']) +
                abs($total['fats'] - $targets['fats']);

            // Check if this is the best combination so far
            if ($difference < $this->bestDifference) {
                $this->bestDifference = $difference;
                $this->bestCombination = $selected;
            }

            return;
        }

        // Get the current ingredient's variants
        $ingredientKeys = array_keys($ingredients);
        $ingredientName = $ingredientKeys[$index];

        // Try each variant
        foreach ($ingredients[$ingredientName] as $variant) {
            $selected[$ingredientName] = $variant;
            $this->findBestCombination($ingredients, $targets, $selected, $index + 1);
        }
    }

    public function showUsersList(UserDataTable $dataTable) {
        return $dataTable->render('users-list');
    }

    public function createUser(Request $request) {
        $userData = [
            'goal' => $request->input('goal'),
            'height' => $request->input('height'),
            'weight' => $request->input('weight'),
            'age' => $request->input('age'),
            'gender' => $request->input('gender'),
            'activity' => $request->input('activity'),
            'insulin_resistance' => $request->input('insulin_resistance'),
            'meals_num' => $request->input('meals_num'),
            'tolerance_calories' => $request->input('tolerance_calories'),
            'tolerance_proteins' => $request->input('tolerance_proteins'),
            'tolerance_fats' => $request->input('tolerance_fats'),
            'days' => $request->input('days'),
        ];

        $user = $this->userService->addUser($userData);
        $macros = $this->userService->getMacrosForUser($user);

        return redirect()->route('assign-recipes-to-user', ['userId' => $user->id]);
    }

}
