<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Models\Foodstuff;
use App\Models\Recipe;
use App\Models\RecipeFoodstuff;
use App\Models\User;
use App\Services\RecipeFoodstuffService;
use App\Services\UserService;
use App\Services\FoodstuffCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UserController extends Controller
{
    protected UserService $userService;
    protected RecipeFoodstuffService $recipefoodstuffService;
    protected FoodstuffCategoryService $foodstuffCategoryService;

    public function __construct() {
        $this->userService = new UserService();
        $this->recipefoodstuffService= new RecipeFoodstuffService();
        $this->foodstuffCategoryService= new FoodstuffCategoryService();
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
        $response = Http::timeout(10000)->post('https://fity-algorithm.fly.dev/meal-plan', [
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
        foreach($data['daily_plans'] as &$day){
            foreach( $day['meals'] as &$meal){
                $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($meal['same_meal_id']);
                $foodstuffsData = [];
                $input = $meal['holders'];
                $pairs = explode(' | ', $input);
                $holders = [];
                foreach ($pairs as $pair) {
                    list($key, $value) = explode(' - ', $pair);
                    /* $holders[(int)$key] = (int)$value;*/
                    $holderFoodStuffRecipe =RecipeFoodstuff::where('foodstuff_id', (int)$key)
                        ->where('recipe_id',$meal['same_meal_id'])->first();

                    if($holderFoodStuffRecipe)
                    {
                        $singleHolder = [
                            'id' => (int)$key,
                            'name' =>Foodstuff::find((int)$key)->name,
                            'amount' => (int)$value,
                            'p' => $holderFoodStuffRecipe->proteins_holder,
                            'f' => $holderFoodStuffRecipe->fats_holder,
                            'c' => $holderFoodStuffRecipe->carbohydrates_holder
                        ];

                        array_push($holders,$singleHolder);
                    }
                }

                foreach ($foodstuffs as $foodstuff){
                    if($foodstuff->proteins_holder == 0 && $foodstuff->fats_holder == 0 && $foodstuff->carbohydrates_holder == 0) {
                        $foodstuffData = [];
                        $foodstuffData['amount'] = $foodstuff['amount'];
                        $foodstuffData['name'] = Foodstuff::find($foodstuff->foodstuff_id)->name;
                        array_push($foodstuffsData,$foodstuffData);
                    }

                }
                $meal['foodstuffs'] = $foodstuffsData;
                $meal['holders'] = $holders;
            }
        }

        return view('user-recipes', compact('user', 'target', 'data'));
    }

    public function showUsersList(UserDataTable $dataTable) {
        return $dataTable->render('users-list');
    }

    public function createUser(Request $request) {
        $answers = '{
	"answers": [
		{
			"index": 0,
			"dataType": "choice",
			"value": "Redukcija telesne mase",
			"detail": null,
			"dataValue": "reduction"

		},
		{
			"index": 0,
			"dataType": "height",
			"value": "100",
			"detail": null,
			"dataValue": "null"
		},
		{
			"index": 1,
			"dataType": "weight",
			"value": "66.0",
			"detail": null,
			"dataValue": "null"
		},
		{
			"index": 2,
			"dataType": "age",
			"value": "40",
			"detail": null,
			"dataValue": "null"
		},
		{
			"index": 3,
			"dataType": "gender",
			"value": "Žensko",
			"detail": null,
			"dataValue": "f"
		},
		{
			"index": 0,
			"dataType": "choice",
			"value": "Nimalo aktivni",
			"detail": null,
			"dataValue": "1.2"
		},
		{
			"index": 1,
			"dataType": "choice",
			"value": "Ne",
			"detail": null,
			"dataValue": "No"
		}
	]
}';

        $anwsers = json_decode($answers);
        /*$userAnswers = [];*/
        /*dd($anwsers);*/
        $userData = [
            'goal' =>$anwsers->answers[0]->dataValue,
            'height' => $anwsers->answers[1]->value,
            'weight' => $anwsers->answers[2]->value,
            'age' => $anwsers->answers[3]->value,
            'gender' => $anwsers->answers[4]->dataValue,
            'activity' => $anwsers->answers[5]->dataValue,
            'insulin_resistance' => $anwsers->answers[6]->dataValue,
            'meals_num' => 3,
            'tolerance_calories' => 50,
            'tolerance_proteins' => 5,
            'tolerance_fats' => 5,
            'days' => 7,
        ];
        dd($userData);
        $user = $this->userService->addUser($userData);
       /* $macros = $this->userService->getMacrosForUser($user);*/
        $foodstuff_category = $this->foodstuffCategoryService->getFoodstuffCategories();

       /* dd($foodstuff_category);*/
        foreach ($foodstuff_category as  $category){
            echo "Kategorijae " .$category->name."<br>";
            foreach($category->foodstuffsOption as $foodstuff){
                echo "Namirnice: " . $foodstuff->name . "<br>";
            }
            echo "------------------------<br><br>";
        }
        /*dd($user);*/
        return redirect()->route('assign-recipes-to-user', ['userId' => $user->id]);
    }

}
