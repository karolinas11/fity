<?php

namespace App\Http\Controllers;

use App\Models\Foodstuff;
use App\Models\Recipe;
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

    public function __construct() {
        $this->userService = new UserService();
        $this->recipefoodstuffService= new RecipeFoodstuffService();
    }

    public function runPythonScript()
    {
        $scriptPath = storage_path('python/constraints.py');
        shell_exec("python3 " . escapeshellarg($scriptPath));
    }

    public function showAddUser()
    {
        $this->runPythonScript();
        return view('create-user');
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
        ];

        $user = $this->userService->addUser($userData);
        return redirect()->route('assign-recipes-to-user', ['userId' => $user->id]);
    }

    public function assignRecipesToUser($userId) {


        $user = User::find($userId);
        $target = $this->userService->getMacrosForUser($user);

//        dd($target);

//        $response = Http::timeout(10000)->post('https://fity-algorithm.fly.dev/meal-plan', [
//            'target_calories' => $target['calories'],
//            'target_protein' => $target['proteins'],
//            'target_fat' => $target['fats'],
//            'meals_num' => $user->meals_num,
//            'tolerance_calories' => $user->tolerance_calories,
//            'tolerance_proteins' => $user->tolerance_proteins,
//            'tolerance_fats' => $user->tolerance_fats,
//        ]);
//
//        if ($response->successful()) {
//            $data = $response->json();
//        } else {
//            $error = $response->body();
//            echo $error;
//        }

        $data = [
            "daily_plans" => [
                [
                    "day" => 1,
                    "meals" => [
                        [
                            "meal_name" => "Namazane galete",
                            "same_meal_id" => 39,
                            "category" => 3,
                            "calories" => 531.2,
                            "protein" => 13.26,
                            "carbs" => 88.8,
                            "fat" => 10.53,
                        ],
                        [
                            "meal_name" => "Ručak 5 - 100",
                            "same_meal_id" => 14,
                            "category" => 2,
                            "calories" => 474.76,
                            "protein" => 22.612,
                            "carbs" => 59.883,
                            "fat" => 11.927,
                        ],
                        [
                            "meal_name" => "Jajakado sendvič",
                            "same_meal_id" => 82,
                            "category" => 1,
                            "calories" => 314.8,
                            "protein" => 12.22,
                            "carbs" => 21.25,
                            "fat" => 19.913,
                        ],
                        [
                            "meal_name" => "Ručak 21 - 100",
                            "same_meal_id" => 99,
                            "category" => 2,
                            "calories" => 639.17,
                            "protein" => 31.388,
                            "carbs" => 26.536,
                            "fat" => 70.08,
                        ],
                    ],
                    "total_calories" => 1959.93,
                    "total_protein" => 79.48,
                    "total_fat" => 112.45,
                ],
                [
                    "day" => 2,
                    "meals" => [
                        [
                            "meal_name" => "Bademi sa urmama",
                            "same_meal_id" => 35,
                            "category" => 3,
                            "calories" => 198.9,
                            "protein" => 3.345,
                            "carbs" => 5.185,
                            "fat" => 39.675,
                        ],
                        [
                            "meal_name" => "Losos tortilja sa jajima - 430",
                            "same_meal_id" => 78,
                            "category" => 1,
                            "calories" => 597.85,
                            "protein" => 39.193,
                            "carbs" => 36.107,
                            "fat" => 28.909,
                        ],
                        [
                            "meal_name" => "Ručak 42 - 100",
                            "same_meal_id" => 120,
                            "category" => 2,
                            "calories" => 617.47,
                            "protein" => 36.11,
                            "carbs" => 21.567,
                            "fat" => 43.87,
                        ],
                        [
                            "meal_name" => "Ručak 13 - 100",
                            "same_meal_id" => 21,
                            "category" => 2,
                            "calories" => 462.96,
                            "protein" => 38.972,
                            "carbs" => 55.875,
                            "fat" => 6.165,
                        ],
                    ],
                    "total_calories" => 1877.18,
                    "total_protein" => 117.62,
                    "total_fat" => 118.619,
                ],
            ],
        ];
//         dd($data);

         foreach($data['daily_plans'] as &$day){
             foreach( $day['meals'] as &$meal){
                 $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($meal['same_meal_id']);
                 $foodstuffsData=[];
                 foreach ($foodstuffs as $foodstuff){
                     $foodstuffData=[];
                     $foodstuffData['amount']=$foodstuff['amount'];
                     $foodstuffData['name']=Foodstuff::find($foodstuff->foodstuff_id)->name;
                    // echo $foodstuff->amount +++++
                     //$foodstuffData = Foodstuff::find($foodstuff->foodstuff_id);
                     //echo $foodstuffData;
                     array_push($foodstuffsData,$foodstuffData);
                 }
                // echo json_encode($foodstuffsData);
                 $meal['foodstuffs']=$foodstuffsData;
             }
        }
         //dd($data);

        return view('user-recipes', compact('user', 'target', 'data'));

    }

}
