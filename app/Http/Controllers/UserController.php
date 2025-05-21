<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Models\Foodstuff;
use App\Models\Recipe;
use App\Models\RecipeFoodstuff;
use App\Models\User;
use App\Models\UserRecipe;
use App\Models\UserWater;
use App\Services\AuthService;
use App\Services\RecipeFoodstuffService;
use App\Services\UserAllergyService;
use App\Services\UserService;
use App\Services\UserWaterService;
use App\Services\UserRecipeService;
use App\Services\FoodstuffCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UserController extends Controller
{
    protected UserService $userService;
    protected RecipeFoodstuffService $recipeFoodstuffService;
    protected UserRecipeService $userRecipeService;
    protected UserAllergyService $userAllergyService;
    protected RecipeFoodstuffService $recipefoodstuffService;
    protected FoodstuffCategoryService $foodstuffCategoryService;
    protected AuthService $authService;
    protected UserWaterService $userWaterService;
//    protected $firebaseAuth;

    public function __construct() {
        $this->userService = new UserService();
        $this->recipeFoodstuffService= new RecipeFoodstuffService();
        $this->userWaterService= new UserWaterService();
        $this->userRecipeService= new UserRecipeService();
        $this->userAllergyService= new UserAllergyService();
        $this->recipefoodstuffService= new RecipeFoodstuffService();
        $this->foodstuffCategoryService= new FoodstuffCategoryService();
//        $factory = (new Factory)->withServiceAccount(base_path('fity-8a542-firebase-adminsdk-fbsvc-3845d64334.json'));
//        $this->firebaseAuth = $factory->createAuth();
        $this->authService = new AuthService();
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
            'days' => $user->days
        ]);


        $data = $response->json();
        foreach($data['daily_plans'] as &$day) {
            $dayCalories = $dayProteins = $dayFats = 0;
            foreach($day['meals'] as &$meal) {
                $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($meal['same_meal_id']);
                $meal['foodstuffs'] = $foodstuffs;
                $dayCalories += $meal['calories'];
                $dayProteins += $meal['proteins'];
                $dayFats += $meal['fats'];
            }
            $day['calories'] = $dayCalories;
            $day['proteins'] = $dayProteins;
            $day['fats'] = $dayFats;
        }

        dd($data);
        return view('user-recipes', compact('user', 'target', 'data'));
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

    public function updateUserWater(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Authorization header missing or invalid'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $this->userWaterService->updateUserWater($userId, $request->water);
    }

    public function updateUserRecipeStatus(Request $request) {
        $this->userRecipeService->updateUserRecipeStatus($request->userId, $request->recipeId, $request->status);
    }

    public function addAllergyData(Request $request) {
        $allergyData = [
           'user_id' => $request->input('userId'),
           'foodstuff_id' => $request->input('foodstuffId')
        ];
        $this->userAllergyService->addUserAllergy($allergyData);
    }

    public function assignFirebaseUid(Request $request)
    {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        $this->userService->assignFirebaseUid($request->userId, $firebaseUid);
        return response()->json(['message' => 'Firebase UID assigned successfully']);
    }

    public function getRecipesByUserIdAndWeek(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Authorization header missing or invalid'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $recipes = $this->userRecipeService->getUserRecipesByDate($userId, $request->startDate, $request->endDate);
        return response()->json($recipes);
    }

    public function getRecipeByUserIdAndRecipeId(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Authorization header missing or invalid'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $recipe = $this->userRecipeService->getUserRecipeByUserIdAndRecipeId($userId, $request->recipeId);
        return response()->json($recipe);
    }

    public function getUserCalories(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Authorization header missing or invalid'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $target = $this->userService->getMacrosForUser(User::find($userId));
        return response()->json($target);
    }

}
