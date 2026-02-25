<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Models\Foodstuff;
use App\Models\FoodstuffCategory;
use App\Models\Photo;
use App\Models\Recipe;
use App\Models\RecipeFoodstuff;
use App\Models\Scope;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\UserAllergy;
use App\Models\UserRecipe;
use App\Models\UserRecipeFoodstuff;
use App\Models\UserSchedule;
use App\Models\UserWater;
use App\Models\UserWeight;
use App\Repositories\UserRecipeRepository;
use App\Services\AuthService;
use App\Services\PhotoService;
use App\Services\RecipeFoodstuffService;
use App\Services\RecipeService;
use App\Services\ScopeService;
use App\Services\UserAllergyService;
use App\Services\UserService;
use App\Services\UserWaterService;
use App\Services\UserRecipeService;
use App\Services\FoodstuffCategoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Google\Client as GoogleClient;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

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
    protected ScopeService $scopeService;
    protected PhotoService $photoService;
    protected UserRecipeRepository $userRecipeRepository;
    protected RecipeService $recipeService;

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
        $this->scopeService = new ScopeService();
        $this->photoService = new PhotoService();
        $this->userRecipeRepository = new UserRecipeRepository();
        $this->recipeService = new RecipeService();
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
            'days'=>$request->input('days'),
            'macros_type'=>$request->input('macros_type'),
            'name' => $request->input('name'),
        ];

        $userId= $request->input('user_id');
        $user = $this->userService->editUser($userData, $userId);

        /* $target = $this->userService->getMacrosForUser($user);*/

        if(!$user){

            return response()->json(['success' => false, 'message'=> 'Korisnik nije pronadjen!'], 200);
        }
        if($user->macros_type == '1') {
            $target = $this->userService->getMacrosForUser($user);
        } else {
            $target = $this->userService->getMacrosForUser2($user);
        }
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
            'macros_type' => $request->input('macros_type'),
            'name' => $request->input('name'),
            'is_test' => $request->input('is_test')
        ];

        $user = $this->userService->addUser($userData);
        //dd($user);
        return redirect()->route('assign-recipes-to-user', ['userId' => $user->id]);
    }

    public function assignRecipesToUser($userId) {

        $user = User::find($userId);
//        if($user->macros_type == '1') {
//            $target = $this->userService->getMacrosForUser($user);
//        } else {
            $target = $this->userService->getMacrosForUser2($user);
//        }

        $userAllergies = UserAllergy::where('user_id', $userId)->get();
        $allergyIds = [];
        foreach ($userAllergies as $userAllergy) {
//            if(Foodstuff::where('id', $userAllergy->foodstuff_id)->get()->first()->foodstuff_category_id == 6) continue;
            $allergyIds[] = $userAllergy->foodstuff_id;
        }

        $response = Http::timeout(10000)
            ->withoutVerifying()
            ->post('https://algo.getfity.app/meal-plan', [
                'target_calories' => $target['calories'],
                'target_protein' => $target['proteins'],
                'target_fat' => $target['fats'],
                'meals_num' => $user->meals_num,
                'tolerance_calories' => $user->tolerance_calories,
                'tolerance_proteins' => $user->tolerance_proteins,
                'tolerance_fats' => $user->tolerance_fats,
                'days' => 30,
                'allergy_holder_ids' => $allergyIds
            ]);


        $data = $response->json();

        $i = 0;
        for($k = 0; $k < 5; $k++) {
            foreach ($data['daily_plans'] as $day) {
                if (!$day['exists']) continue;
                $date = date('Y-m-d', strtotime('+' . $i . ' days'));
                $i++;
                $lunch = false;
                foreach ($day['meals'] as $meal) {
//                if($meal['same_meal_id'] == 33) {
//                    continue;
//                }
                    $r = Recipe::find($meal['same_meal_id']);
//                    $userRecipe = UserRecipe::create([
//                        'user_id' => $userId,
//                        'recipe_id' => $meal['same_meal_id'],
//                        'status' => 'active',
//                        'date' => $date,
//                        'type' => $lunch && $r->type == 2 ? 4 : $r->type
//                    ]);
                    if ($r->type == 2) {
                        $lunch = true;
                    }
                    $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($meal['same_meal_id']);
                    foreach ($foodstuffs as $foodstuff) {
                        if ($foodstuff->proteins_holder == 0 && $foodstuff->fats_holder == 0 && $foodstuff->carbohydrates_holder == 0) {
//                            UserRecipeFoodstuff::create([
//                                'user_recipe_id' => $userRecipe->id,
//                                'foodstuff_id' => $foodstuff->foodstuff_id,
//                                'amount' => $foodstuff->amount,
//                                'purchased' => 0
//                            ]);
                        }
                    }

                    foreach ($meal['holder_quantities'] as $key => $holder) {
//                        UserRecipeFoodstuff::create([
//                            'user_recipe_id' => $userRecipe->id,
//                            'foodstuff_id' => $key,
//                            'amount' => $holder,
//                            'purchased' => 0
//                        ]);
                    }
                }
            }
        }

        foreach($data['daily_plans'] as &$day) {
            $dayCalories = $dayProteins = $dayFats = $dayCarbs = 0;
            foreach($day['meals'] as &$meal) {
                $meal['carbohydrates'] = 0;
                $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($meal['same_meal_id']);
                foreach ($meal['holder_quantities'] as $key => $holder) {
                    $f = Foodstuff::find($key);
                    $meal['carbohydrates'] += $f->carbohydrates * $holder / 100;
                }
                foreach ($foodstuffs as $foodstuff) {
                    if($foodstuff->proteins_holder == 0 && $foodstuff->fats_holder == 0 && $foodstuff->carbohydrates_holder == 0) {
                        $f = Foodstuff::find($foodstuff->foodstuff_id);
                        $meal['carbohydrates'] += $f->carbohydrates * $foodstuff->amount / 100;
                    }
                }
                $meal['foodstuffs'] = $foodstuffs;
                $dayCalories += $meal['calories'];
                $dayProteins += $meal['proteins'];
                $dayFats += $meal['fats'];

            }
            $day['calories'] = $dayCalories;
            $day['proteins'] = $dayProteins;
            $day['fats'] = $dayFats;
        }

        return view('user-recipes', compact('user', 'target', 'data', 'userAllergies'));
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
            'macros_type' => $request->input('macros_type')
        ];

        $user = $this->userService->addUser($userData);

        return redirect()->route('assign-recipes-to-user', ['userId' => $user->id]);
    }

    public function updateUserWater(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $this->userWaterService->updateUserWater($userId, $request->water);
    }

    public function getUserWater(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;

        $start = null;
        $end = null;

        if ($request->filled(['startDate', 'endDate'])) {
            $start = Carbon::parse($request->input('startDate'))->format('Y-m-d');
            $end = Carbon::parse($request->input('endDate'))->format('Y-m-d');
        }

        $query = UserWater::where('user_id', $userId);

        if ($start && $end) {
            $query->whereBetween('date', [$start, $end]);
        }

//        if($start == $end) {
//            $userWater = $query->first();
//        } else {
            $userWater = $query->get();
//        }
        return response()->json($userWater);
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
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = $this->userService->assignFirebaseUid($request->userId, $firebaseUid, $request->email, $request->name);

        $name = explode(' ', $request->name);
        $firstName = $name[0];
        $lastName = $name[1] ?? '';
        $customFields = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => $user->gender == 'm'? 'Male': 'Female',
            'age' => $user->age,
            'name' => $request->name,
            'member_type' => $user->type == 2? 'Starter': 'Regular'
        ];

        $this->userService->updateSubscriberFields($request->email, $customFields);

        return response()->json(['user' => User::find($request->userId)]);
    }

    public function getRecipesByUserIdAndWeek(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = User::where('firebase_uid', $firebaseUid)->first();
        $userId = $user->id;

        if($user->landed == 0) {
            if($user->is_subscribed == 0) {
                $customFields = [
                    'subscription_type' => 'Trial'
                ];
            } else if($user->is_subscribed == 1) {
                $customFields = [
                    'subscription_type' => 'Monthly'
                ];
            }

            $this->userService->updateSubscriberFields($user->email, $customFields);
        }

        $user->landed = 1;
        $user->save();
        $recipes = $this->userRecipeService->getUserRecipesByDate($userId, $request->startDate, $request->endDate);
        return response()->json($recipes);
    }

    public function getRecipeByUserIdAndRecipeId(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $recipe = $this->userRecipeService->getUserRecipeByUserIdAndRecipeId($request->input('recipeId'), $request->input('screen'), $userId);
        if ($recipe->bookmarked_status == 1) {
            $recipe->bookmarked_status = 'bookmarked';
        } else if ($recipe->bookmarked_status == -1) {
            $recipe->bookmarked_status = 'deleted';
        } else {
            $recipe->bookmarked_status = 'active';
        }

        $userAllergies = UserAllergy::where('user_id', $userId)->get();
        $hiddenFoodstuffs = [];

        foreach ($userAllergies as $userAllergy) {
            if(Foodstuff::where('id', $userAllergy->foodstuff_id)->get()->first()->foodstuff_category_id == 6) {
                $hiddenFoodstuffs[] = $userAllergy->foodstuff_id;
            }
        }

        $recipe->hidden_foodstuffs = $hiddenFoodstuffs;

        return response()->json($recipe);
    }

    public function getUserCalories(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $target = $this->userService->getMacrosForUser2(User::find($userId));
        return response()->json($target);
    }

    public function addScope(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $scopeData = [
            'user_id' => $userId,
            'name' => $request->input('scope_name'),
            'metric' => $request->input('scope_metric'),
            'dimension' => $request->input('scope_dimension'),
        ];
        $scope = $this->scopeService->addScope($scopeData);
        return response()->json($scope);
    }

    public function getUserScopes(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $scopes = $this->scopeService->getUserScopes($userId);
        return response()->json($scopes);
    }

    public function getUserScopeFirst(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $scope = Scope::where('user_id', $userId)
            ->where('name', $request->input('scope_name'))
            ->first();
        return response()->json($scope);
    }

    public function getUserScopeLast(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $scope = Scope::where('user_id', $userId)
            ->where('name', $request->input('scope_name'))
            ->latest()
            ->first();
        return response()->json($scope);
    }

    public function addPhoto(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $base64Image = $request->input('photo');
        if (!$base64Image) {
            return response()->json(['error' => 'No image data provided'], 422);
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $image = substr($base64Image, strpos($base64Image, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                return response()->json(['error' => 'Invalid image type'], 422);
            }

            $image = base64_decode($image);

            if ($image === false) {
                return response()->json(['error' => 'Base64 decode failed'], 422);
            }

            $filename = 'user_' . $user->id . '_' . Str::random(10) . '.' . $type;

            $path = 'user_photos/' . $filename;
            Storage::disk('public')->put($path, $image);

            $photoData = [
                'user_id' => $user->id,
                'type' => $request->input('type'),
                'path' => $path,
            ];

            $photo = $this->photoService->addPhoto($photoData);

            return response()->json($photo);
        } else {
            return response()->json(['error' => 'Invalid image format'], 422);
        }
    }

    public function getUserPhotos(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $photos = $this->photoService->getUserPhotos($userId);
        return response()->json($photos);
    }

    public function getUserPhotoFirst(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $photo = Photo::where('user_id', $userId)
            ->where('type', $request->input('type'))
            ->first();
        return response()->json($photo);
    }

    public function getUserPhotoLast(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $photo = Photo::where('user_id', $userId)
            ->where('type', $request->input('type'))
            ->latest()
            ->first();
        return response()->json($photo);
    }

    public function getUser(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = User::where('firebase_uid', $firebaseUid)->first();
        $user->macros = $this->userService->getMacrosForUser2($user);
        $activity = '';
        switch ($user->activity) {
            case 1.2:
                $activity = 'Nimalo aktivni';
                break;
            case 1.375:
                $activity = 'Slabo aktivni';
                break;
            case 1.55:
                $activity = 'Srednje aktivni';
                break;
            case 1.725:
                $activity = 'Vrlo aktivni';
                break;
            case 1.95:
                $activity = 'Ekstremno aktivni';
                break;
        }
        $user->activity = $activity;
        $userAllergies = UserAllergy::where('user_id', $user->id)->get();
        $removedFoodstuffs = [];
        foreach($userAllergies as $userAllergy) {
            $allergy = Foodstuff::find($userAllergy->foodstuff_id);
            $removedFoodstuffs[] = $allergy;
        }
        $user->removedFoodstuffs = $removedFoodstuffs;
        $meals = ['doručak, ručak, večera'];
        if($user->meals_num > 3) {
            array_push($meals, 'užina 1');
        }
        if($user->meals_num > 4) {
            array_push($meals, 'užina 2');
        }
        $user->meals = $meals;
        return response()->json($user);
    }

    public function appleSignInCallback(Request $request) {
        $queryParams = http_build_query($request->all());
        $intentUrl = "intent://callback?' . $queryParams . '#Intent;package=rs.fity;scheme=signinwithapple;end";
        return redirect()->away($intentUrl);
    }

    public function addUserWeight(Request $request)
    {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));

        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Datum koji dolazi iz requesta
        $createdAt = Carbon::parse($request->input('created_at'))->startOfDay();

        // Provera da li već postoji zapis za taj dan
        $existing = UserWeight::where('user_id', $user->id)
            ->whereDate('created_at', $createdAt)
            ->first();

        if ($existing) {
            // Ažurira postojeći zapis
            $existing->update([
                'weight' => $request->input('weight'),
                'created_at' => $createdAt, // opciono — ako želiš da poravna datum
            ]);
            return response()->json($existing);
        }

        // Kreira novi zapis
        $userWeight = UserWeight::create([
            'user_id' => $user->id,
            'weight' => $request->input('weight'),
            'created_at' => $createdAt,
        ]);

        return response()->json($userWeight);
    }

    public function getUserWeights(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $userWeights = UserWeight::where('user_id', $userId)->get();
        return response()->json($userWeights);
    }

    public function deleteUserPhoto(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $photo = Photo::find($request->input('photoId'));
        $photo->delete();
        return response()->json('success', 200);
    }

    public function getShopFoodstuffs(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $foodstuffs = collect();

        $recipes = $this->userRecipeRepository->getUserRecipes($userId, $startDate, $endDate);

        foreach ($recipes as $recipe) {
            foreach ($recipe->foodstuffs as &$foodstuff) {
                $foodstuffId = $foodstuff->foodstuff_id;
                $fullFoodstuffModel = Foodstuff::find($foodstuffId);
                $fullFoodstuffModel->foodstuff_category = FoodstuffCategory::where('id', $fullFoodstuffModel->foodstuff_category_id)->get()->first()->name;
                $foodstuff->full_model = $fullFoodstuffModel;
                $foodstuff->foodstuff_id = $foodstuffId;
                $foodstuff->amount = $foodstuff->amount;
                $foodstuff->purchased = $foodstuff->purchased;
                $foodstuff->full_model->imageUrl = $fullFoodstuffModel->featured_image;
                if($fullFoodstuffModel->has_piece == 1) {
                    $pieces = $foodstuff->amount / $fullFoodstuffModel->piece_amount;
                    $output = round($pieces);
                    if($pieces == 1) {
                        $output .= ' ' . $fullFoodstuffModel->piece_1;
                    } else if($pieces > 1 && $pieces < 5) {
                        $output .= ' ' . $fullFoodstuffModel->pieces_2_4;
                    } else {
                        $output .= ' ' . $fullFoodstuffModel->pieces_5_9;
                    }
                    $foodstuff->full_model->description = $output;
                } else {
                    $foodstuff->full_model->description = null;
                }
                $foodstuffs->push($foodstuff);
            }
        }

        $foodstuffsFinal = $foodstuffs->groupBy('foodstuff_id')->map(function ($group) {
            return [
                'name' => $group->first()->full_model->name,
                'category' => $group->first()->category,
                'amount' => $group->where('purchased', 0)->sum('amount'),
                'ingredient' => $group->first()->full_model,
                'bought' => $group->every(fn ($f) => $f->purchased == 1),
                'unit' => 'g',
            ];
        })->values();

        return response()->json($foodstuffsFinal);
    }

    public function getRecipeShopFoodstuffs(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $recipe = UserRecipe::find($request->input('recipeId'));
        $foodstuffs = collect();

        foreach ($recipe->foodstuffs as &$foodstuff) {
            $foodstuffId = $foodstuff->foodstuff_id;
            $fullFoodstuffModel = Foodstuff::find($foodstuffId);
            $fullFoodstuffModel->foodstuff_category = FoodstuffCategory::where('id', $fullFoodstuffModel->foodstuff_category_id)->get()->first()->name;
            $foodstuff->full_model = $fullFoodstuffModel;
            $foodstuff->foodstuff_id = $foodstuffId;
            $foodstuff->amount = $foodstuff->amount;
            $foodstuff->purchased = $foodstuff->purchased;
            $foodstuff->full_model->amount = $foodstuff->amount;
            $foodstuff->full_model->imageUrl = $fullFoodstuffModel->featured_image;
            if($fullFoodstuffModel->has_piece == 1) {
                $pieces = $foodstuff->amount / $fullFoodstuffModel->piece_amount;
                $output = round($pieces);
                if($pieces == 1) {
                    $output .= ' ' . $fullFoodstuffModel->piece_1;
                } else if($pieces > 1 && $pieces < 5) {
                    $output .= ' ' . $fullFoodstuffModel->pieces_2_4;
                } else {
                    $output .= ' ' . $fullFoodstuffModel->pieces_5_9;
                }
                $foodstuff->full_model->description = $output;
            } else {
                $foodstuff->full_model->description = null;
            }
            $foodstuffs->push($foodstuff);
        }

        $foodstuffsFinal = $foodstuffs->groupBy('foodstuff_id')->map(function ($group) {
            return [
                'name' => $group->first()->full_model->name,
                'amount' => $group->where('purchased', 0)->sum('amount'),
                'ingredient' => $group->first()->full_model,
                'bought' => $group->every(fn ($f) => $f->purchased == 1),
                'unit' => 'g',
            ];
        })->values();

        $recipe->ingredients = $foodstuffsFinal;

        $ogRecipe = Recipe::find($recipe->recipe_id);

        $recipe->title = $ogRecipe->name;
        $recipe->imageUrl = $ogRecipe->featured_image;

        return response()->json($recipe);
    }

    public function updateShopFoodstuffs(Request $request)
    {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->first();
        $userRecipes = UserRecipe::where('user_id', $user->id)->get();
        foreach ($userRecipes as $recipe) {
            foreach ($recipe->foodstuffs as $foodstuff) {
                if($foodstuff->foodstuff_id == $request->input('foodstuffId')) {
                    $foodstuff->purchased = $request->input('purchased');
                    $foodstuff->save();
                }
            }
        }

        return response()->json('success', 200);
    }

    public function deleteUserWeights(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        UserWeight::find($request->input('weightId'))->delete();
        return response()->json('success', 200);
    }

    public function deleteUserScope(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        Scope::find($request->input('scopeId'))->delete();
        return response()->json('success', 200);
    }

    public function updateUser(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->first();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->save();

        $base64Image = $request->input('avatar');

        if ($base64Image && preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $image = substr($base64Image, strpos($base64Image, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                return response()->json(['error' => 'Invalid image type'], 422);
            }

            $image = base64_decode($image);

            if ($image === false) {
                return response()->json(['error' => 'Base64 decode failed'], 422);
            }

            $filename = 'user_' . $user->id . '_' . Str::random(10) . '.' . $type;

            $path = 'user_photos/' . $filename;
            Storage::disk('public')->put($path, $image);

            $user->avatar = $path;
            $user->save();
            return response()->json($user);
        }

        return response()->json($user);
    }

    public function changeUserRecipe(Request $request)
    {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->first();
        $userRecipe = UserRecipe::find($request->input('recipeId'));
        $userRecipe->status = $request->input('status');
        $userRecipe->save();

        $data = $this->recipeService->getRecipeAlternatives($userRecipe);

        $usefullCombinations = $data['combinations'];
        $cal = $data['cal'];
        $prot = $data['prot'];
        $fat = $data['fat'];
        //$ch = $data['ch'];

        if(count($usefullCombinations) > 0) {
            shuffle($usefullCombinations);
            $randomNewRecipe = Recipe::where('id', $usefullCombinations[0]['recipe'])->get()[0];
            $fixCal = 0;
            $fixProt = 0;
            $fixFat = 0;
            //$fixCh = 0;
            $newHolders = [];
            foreach ($randomNewRecipe->foodstuffs as $fm) {
                $f = RecipeFoodstuff::where('foodstuff_id', $fm->id)
                    ->where('recipe_id', $randomNewRecipe->id)
                    ->get()[0];
                if($f->proteins_holder == 0 && $f->fats_holder == 0 && $f->carbohydrates_holder == 0) {
                    $fixCal += $f->amount * ($fm->calories / 100);
                    $fixProt += $f->amount * ($fm->proteins / 100);
                    $fixFat += $f->amount * ($fm->fats / 100);
                    //$fixCh += $f->amount * ($fm->carbohydrates / 100);
                } else {
                    $newHolders[] = $fm;
                }
            }

            $newCombinations = [];

            if(count($newHolders) == 1) {
                $step = $newHolders[0]->step?? $newHolders[0]->min;
                for($i = $newHolders[0]->min; $i <= $newHolders[0]->max; $i += $step) {
                    $newCombinations[] = [
                        'calories' => $i * ($newHolders[0]->calories / 100) + $fixCal,
                        'proteins' => $i * ($newHolders[0]->proteins / 100) + $fixProt,
                        'fats' => $i * ($newHolders[0]->fats / 100) + $fixFat,
                        //'carbohydrates' => $i * ($newHolders[0]->carbohydrates / 100) + $fixCh,
                        'foodstuff_id' => $newHolders[0]->id,
                        'amounts' => $i,
                        'recipe_id' => $randomNewRecipe->id
                    ];
                }
            } else if(count($newHolders) == 2) {
                $step = $newHolders[0]->step?? $newHolders[0]->min;
                $step2 = $newHolders[1]->step?? $newHolders[1]->min;
                for($i = $newHolders[0]->min; $i <= $newHolders[0]->max; $i += $step) {
                    for ($j = $newHolders[1]->min; $j <= $newHolders[1]->max; $j += $step2) {
                        $newCombinations[] = [
                            'calories' => $i * ($newHolders[0]->calories / 100) + $j * ($newHolders[1]->calories / 100) + $fixCal,
                            'proteins' => $i * ($newHolders[0]->proteins / 100) + $j * ($newHolders[1]->proteins / 100) + $fixProt,
                            'fats' => $i * ($newHolders[0]->fats / 100) + $j * ($newHolders[1]->fats / 100) + $fixFat,
                            //'carbohydrates' => $i * ($newHolders[0]->carbohydrates / 100) + $j * ($newHolders[1]->carbohydrates / 100) + $fixCh,
                            'foodstuff_id' => $newHolders[0]->id . '-' . $newHolders[1]->id,
                            'amounts' => $i . '-' . $j,
                            'recipe_id' => $randomNewRecipe->id
                        ];
                    }
                }
            } else if(count($newHolders) == 3) {
                $step = $newHolders[0]->step?? $newHolders[0]->min;
                $step2 = $newHolders[1]->step?? $newHolders[1]->min;
                $step3 = $newHolders[2]->step?? $newHolders[2]->min;
                for($i = $newHolders[0]->min; $i <= $newHolders[0]->max; $i += $step) {
                    for ($j = $newHolders[1]->min; $j <= $newHolders[1]->max; $j += $step2) {
                        for ($k = $newHolders[2]->min; $k <= $newHolders[2]->max; $k += $step3) {
                            $newCombinations[] = [
                                'calories' => $i * ($newHolders[0]->calories / 100) + $j * ($newHolders[1]->calories / 100) + $k * ($newHolders[2]->calories / 100) + $fixCal,
                                'proteins' => $i * ($newHolders[0]->proteins / 100) + $j * ($newHolders[1]->proteins / 100) + $k * ($newHolders[2]->proteins / 100) + $fixProt,
                                'fats' => $i * ($newHolders[0]->fats / 100) + $j * ($newHolders[1]->fats / 100) + $k * ($newHolders[2]->fats / 100) + $fixFat,
                                //'carbohydrates' => $i * ($newHolders[0]->carbohydrates / 100) + $j * ($newHolders[1]->carbohydrates / 100) + $k * ($newHolders[2]->carbohydrates / 100) + $fixCh,
                                'foodstuff_id' => $newHolders[0]->id . '-' . $newHolders[1]->id . '-' . $newHolders[2]->id,
                                'amounts' => $i . '-' . $j . '-' . $k,
                                'recipe_id' => $randomNewRecipe->id
                            ];
                        }
                    }
                }
            } else {
                dd('0 HOLDERA');
            }

            $best     = null;
            $minDist2 = PHP_INT_MAX;  // čuvamo najmanju kvadratnu distancu

            foreach ($newCombinations as $cand) {
                $dCal  = $cand['calories']      - $cal;
                $dProt = $cand['proteins']      - $prot;
                $dFat  = $cand['fats']          - $fat;
                //$dCh   = $cand['carbohydrates'] - $ch;

                // kvadrat Euklidske distance (bez sqrt jer nam dovoljna komparacija)
                $dist2 = $dCal*$dCal
                    + $dProt*$dProt
                    + $dFat*$dFat;
                    //+ $dCh*$dCh;

                if ($dist2 < $minDist2) {
                    $minDist2 = $dist2;
                    $best     = $cand;
                }
            }

            $newHs = explode('-', $best['foodstuff_id']);
            $newH = array_map('intval', $newHs);
            $newAs = explode('-', $best['amounts']);
            $newA = array_map('intval', $newAs);
            $newRecipe = Recipe::find($best['recipe_id']);

            $newUserRecipe = UserRecipe::create([
                'user_id' => $user->id,
                'recipe_id' => $newRecipe->id,
                'status' => 'active',
                'type' => $userRecipe->type,
                'date' => $userRecipe->date
            ]);

            $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($newRecipe->id);
            foreach ($foodstuffs as $fn) {
                if($fn->proteins_holder == 0 && $fn->fats_holder == 0 && $fn->carbohydrates_holder == 0) {
                    UserRecipeFoodstuff::create([
                        'user_recipe_id' => $newUserRecipe->id,
                        'foodstuff_id' => $fn->foodstuff_id,
                        'amount' => $fn->amount,
                        'purchased' => 0
                    ]);
                }
            }

            for($i = 0; $i < count($newH); $i++) {
                UserRecipeFoodstuff::create([
                    'user_recipe_id' => $newUserRecipe->id,
                    'foodstuff_id' => $newH[$i],
                    'amount' => $newA[$i],
                    'purchased' => 0
                ]);
            }

        } else {
            dd('0 NOVIH KOMBINACIJA');
        }

        return response()->json($userRecipe);
    }

    public function generateNewMealPlan(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return response()->json('success', 200);
    }

    public function updateMealCalendar(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();
//        $user = User::find(351);
        $schedules = $this->decodeSchedule($request->schedule)['chainsLabeled'];

        UserSchedule::create([
            'user_id' => $user->id,
            'schedule' => json_encode($request->schedule)
        ]);

        Log::error('CALENDAR: ' . json_encode($schedules));

        foreach ($schedules as $schedule) {
            $targetDayMeal = $schedule[0];
            $typeDay = explode('–', $targetDayMeal);
            $type = $typeDay[0];
            $day = $typeDay[1];
            $targetDate = $this->userService->nextDateForDay(strtoupper($day));

            $userRecipe = UserRecipe::where('user_id', $user->id)
                ->where('date', $targetDate)
                ->where('type', $type)
                ->where('status', 'active')
                ->get()
                ->first();

            Log::error('UserRecipe ARGS: ' . $targetDate . ' ' . $type . ' ' . $user->id);

            for($e = 1; $e < count($schedule); $e++) {
                $targetDayMeal2 = $schedule[$e];
                $typeDay2 = explode('–', $targetDayMeal2);
                $type2 = $typeDay2[0];
                $day2 = $typeDay2[1];
                $targetDate2 = $this->userService->nextDateForDay(strtoupper($day2));
                $existingRecipe = UserRecipe::where('user_id', $user->id)
                    ->where('date', $targetDate2)
                    ->where('status', 'active')
                    ->where('type', $type2)
                    ->get()
                    ->first();

                Log::error('ARGS: ' . $targetDate2 . ' ' . $type2 . ' ' . $user->id);

                Log::error('ExistingRecipe: ' . json_encode($existingRecipe));


//                if($existingRecipe == null || $userRecipe == null) {
//                    dd($userRecipe, $existingRecipe, $targetDate2, $type2);
//                }
                if ($existingRecipe->recipe_id == $userRecipe->recipe_id) {
                    continue;
                }

                $recipe = Recipe::find($userRecipe->recipe_id);

                $cal = 0;
                $prot = 0;
                $fat = 0;
                //$ch = 0;
                foreach ($existingRecipe->foodstuffs as &$foodstuff) {
                    $f = Foodstuff::where('id', $foodstuff->foodstuff_id)->get()[0];
                    $cal += $foodstuff->amount * ($f->calories / 100);
                    $prot += $foodstuff->amount * ($f->proteins / 100);
                    $fat += $foodstuff->amount * ($f->fats / 100);
                    //$ch += $foodstuff->amount * ($f->carbohydrates / 100);
                }

                $rCal = 0;
                $rProt = 0;
                $rFat = 0;
                //$rCh = 0;

                $holders = [];
                foreach ($recipe->foodstuffs as $fm) {
                    $f = RecipeFoodstuff::where('foodstuff_id', $fm->id)
                        ->where('recipe_id', $recipe->id)
                        ->get()[0];
                    if ($f->proteins_holder == 0 && $f->fats_holder == 0 && $f->carbohydrates_holder == 0) {
                        $rCal += $f->amount * ($fm->calories / 100);
                        $rProt += $f->amount * ($fm->proteins / 100);
                        $rFat += $f->amount * ($fm->fats / 100);
                        //$rCh += $f->amount * ($fm->carbohydrates / 100);
                    } else {
                        $holders[] = $fm;
                    }
                }


//                if (count($holders) > 0) {
//                    $rCalMin = $rProtMin = $rFatMin = $rChMin = 0;
//                    $rCalMax = $rProtMax = $rFatMax = $rChMax = 0;
//
//                    foreach ($holders as $h) {
//                        $rCalMin += $h->min * ($h->calories / 100);
//                        $rProtMin += $h->min * ($h->proteins / 100);
//                        $rFatMin += $h->min * ($h->fats / 100);
//                        $rChMin += $h->min * ($h->carbohydrates / 100);
//
//                        $rCalMax += $h->max * ($h->calories / 100);
//                        $rProtMax += $h->max * ($h->proteins / 100);
//                        $rFatMax += $h->max * ($h->fats / 100);
//                        $rChMax += $h->max * ($h->carbohydrates / 100);
//                    }
//
//                    $combinations[] = [
//                        'caloriesMin' => $rCal + $rCalMin,
//                        'proteinsMin' => $rProt + $rProtMin,
//                        'fatsMin' => $rFat + $rFatMin,
//                        'carbohydratesMin' => $rCh + $rChMin,
//                        'caloriesMax' => $rCal + $rCalMax,
//                        'proteinsMax' => $rProt + $rProtMax,
//                        'fatsMax' => $rFat + $rFatMax,
//                        'carbohydratesMax' => $rCh + $rChMax,
//                        'recipe' => $recipe->id,
//                    ];
//                } else {
//                    $combinations[] = [
//                        'caloriesMin' => $rCal,
//                        'proteinsMin' => $rProt,
//                        'fatsMin' => $rFat,
//                        'carbohydratesMin' => $rCh,
//                        'caloriesMax' => $rCal,
//                        'proteinsMax' => $rProt,
//                        'fatsMax' => $rFat,
//                        'carbohydratesMax' => $rCh,
//                        'recipe' => $recipe->id
//                    ];
//                }
//
//                $usefullCombinations = $combinations;

//                foreach ($combinations as $combination) {
//                    if ($combination['caloriesMin'] <= $cal && $combination['caloriesMax'] >= $cal
//                        && $combination['proteinsMin'] <= $prot && $combination['proteinsMax'] >= $prot
//                        && $combination['fatsMin'] <= $fat && $combination['fatsMax'] >= $fat
//                        && $combination['carbohydratesMin'] <= $ch && $combination['carbohydratesMax'] >= $ch) {
//                        $usefullCombinations[] = $combination;
//                    }
//
//                }

//                if(count($usefullCombinations) == 0) {
//                    dd($usefullCombinations);
//                }

//                dd($combinations, $cal, $prot, $fat, $ch);

                $targetRecipe = $recipe;

                $fixCal = 0;
                $fixProt = 0;
                $fixFat = 0;
                //$fixCh = 0;
                $newHolders = [];
                foreach ($targetRecipe->foodstuffs as $fm) {
                    $f = RecipeFoodstuff::where('foodstuff_id', $fm->id)
                        ->where('recipe_id', $targetRecipe->id)
                        ->get()[0];
                    if($f->proteins_holder == 0 && $f->fats_holder == 0 && $f->carbohydrates_holder == 0) {
                        $fixCal += $f->amount * ($fm->calories / 100);
                        $fixProt += $f->amount * ($fm->proteins / 100);
                        $fixFat += $f->amount * ($fm->fats / 100);
                        //$fixCh += $f->amount * ($fm->carbohydrates / 100);
                    } else {
                        $newHolders[] = $fm;
                    }
                }

                $newCombinations = [];

                if(count($newHolders) == 1) {
                    $step = $newHolders[0]->step?? $newHolders[0]->min;
                    for($i = $newHolders[0]->min; $i <= $newHolders[0]->max; $i += $step) {
                        $newCombinations[] = [
                            'calories' => $i * ($newHolders[0]->calories / 100) + $fixCal,
                            'proteins' => $i * ($newHolders[0]->proteins / 100) + $fixProt,
                            'fats' => $i * ($newHolders[0]->fats / 100) + $fixFat,
                            //'carbohydrates' => $i * ($newHolders[0]->carbohydrates / 100) + $fixCh,
                            'foodstuff_id' => $newHolders[0]->id,
                            'amounts' => $i,
                            'recipe_id' => $targetRecipe->id
                        ];
                    }
                } else if(count($newHolders) == 2) {
                    $step = $newHolders[0]->step?? $newHolders[0]->min;
                    $step2 = $newHolders[1]->step?? $newHolders[1]->min;
                    for($i = $newHolders[0]->min; $i <= $newHolders[0]->max; $i += $step) {
                        for ($j = $newHolders[1]->min; $j <= $newHolders[1]->max; $j += $step2) {
                            $newCombinations[] = [
                                'calories' => $i * ($newHolders[0]->calories / 100) + $j * ($newHolders[1]->calories / 100) + $fixCal,
                                'proteins' => $i * ($newHolders[0]->proteins / 100) + $j * ($newHolders[1]->proteins / 100) + $fixProt,
                                'fats' => $i * ($newHolders[0]->fats / 100) + $j * ($newHolders[1]->fats / 100) + $fixFat,
                                //'carbohydrates' => $i * ($newHolders[0]->carbohydrates / 100) + $j * ($newHolders[1]->carbohydrates / 100) + $fixCh,
                                'foodstuff_id' => $newHolders[0]->id . '-' . $newHolders[1]->id,
                                'amounts' => $i . '-' . $j,
                                'recipe_id' => $targetRecipe->id
                            ];
                        }
                    }
                } else if(count($newHolders) == 3) {
                    $step = $newHolders[0]->step?? $newHolders[0]->min;
                    $step2 = $newHolders[1]->step?? $newHolders[1]->min;
                    $step3 = $newHolders[2]->step?? $newHolders[2]->min;
                    for($i = $newHolders[0]->min; $i <= $newHolders[0]->max; $i += $step) {
                        for ($j = $newHolders[1]->min; $j <= $newHolders[1]->max; $j += $step2) {
                            for ($k = $newHolders[2]->min; $k <= $newHolders[2]->max; $k += $step3) {
                                $newCombinations[] = [
                                    'calories' => $i * ($newHolders[0]->calories / 100) + $j * ($newHolders[1]->calories / 100) + $k * ($newHolders[2]->calories / 100) + $fixCal,
                                    'proteins' => $i * ($newHolders[0]->proteins / 100) + $j * ($newHolders[1]->proteins / 100) + $k * ($newHolders[2]->proteins / 100) + $fixProt,
                                    'fats' => $i * ($newHolders[0]->fats / 100) + $j * ($newHolders[1]->fats / 100) + $k * ($newHolders[2]->fats / 100) + $fixFat,
                                    //'carbohydrates' => $i * ($newHolders[0]->carbohydrates / 100) + $j * ($newHolders[1]->carbohydrates / 100) + $k * ($newHolders[2]->carbohydrates / 100) + $fixCh,
                                    'foodstuff_id' => $newHolders[0]->id . '-' . $newHolders[1]->id . '-' . $newHolders[2]->id,
                                    'amounts' => $i . '-' . $j . '-' . $k,
                                    'recipe_id' => $targetRecipe->id
                                ];
                            }
                        }
                    }
                } else {
                    $newCombinations[] = [
                        'calories' => $fixCal,
                        'proteins' => $fixProt,
                        'fats' => $fixFat,
                        //'carbohydrates' => $fixCh,
                        'foodstuff_id' => '',
                        'amounts' => '',
                        'recipe_id' => $targetRecipe->id
                    ];
                }

                $best     = null;
                $minDist2 = PHP_INT_MAX;  // čuvamo najmanju kvadratnu distancu

                foreach ($newCombinations as $cand) {
                    $dCal  = $cand['calories']      - $cal;
                    $dProt = $cand['proteins']      - $prot;
                    $dFat  = $cand['fats']          - $fat;
                    //$dCh   = $cand['carbohydrates'] - $ch;

                    // kvadrat Euklidske distance (bez sqrt jer nam dovoljna komparacija)
                    $dist2 = $dCal*$dCal
                        + $dProt*$dProt
                        + $dFat*$dFat;
                        //+ $dCh*$dCh;

                    if ($dist2 < $minDist2) {
                        $minDist2 = $dist2;
                        $best     = $cand;
                    }
                }

                $newHs = explode('-', $best['foodstuff_id']);
                $newH = array_map('intval', $newHs);
                $newAs = explode('-', $best['amounts']);
                $newA = array_map('intval', $newAs);
                $newRecipe = Recipe::find($best['recipe_id']);

                $newUserRecipe = UserRecipe::create([
                    'user_id' => $user->id,
                    'recipe_id' => $newRecipe->id,
                    'status' => 'active',
                    'type' => $existingRecipe->type,
                    'date' => $targetDate2
                ]);

                $existingRecipe->status = 'replaced';
                $existingRecipe->save();

                $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($newRecipe->id);
                foreach ($foodstuffs as $fn) {
                    if($fn->proteins_holder == 0 && $fn->fats_holder == 0 && $fn->carbohydrates_holder == 0) {
                        UserRecipeFoodstuff::create([
                            'user_recipe_id' => $newUserRecipe->id,
                            'foodstuff_id' => $fn->foodstuff_id,
                            'amount' => $fn->amount,
                            'purchased' => 0
                        ]);
                    }
                }

                for($i = 0; $i < count($newH); $i++) {
                    UserRecipeFoodstuff::create([
                        'user_recipe_id' => $newUserRecipe->id,
                        'foodstuff_id' => $newH[$i],
                        'amount' => $newA[$i],
                        'purchased' => 0
                    ]);
                }



            }
        }

        return response()->json('success', 200);
    }

    public function repeatMeals(Request $request)
    {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();

        $dates = $request->dates;
        if($request->input('screen')) {
            $re = Recipe::find($request->mealId);
            $rid = $re->id;
            $t = $re->type;
        } else {
            $userRecipe = UserRecipe::find($request->mealId);
            $t = $userRecipe->type;
            $rid = $userRecipe->recipe_id;
        }
//        $user = User::find(337);


        foreach ($dates as $date) {
            $existingRecipe = UserRecipe::where('user_id', $user->id)
                ->where('date', $date)
                ->where('status', 'active')
                ->where('type', $t)
                ->get()
                ->first();
            if ($existingRecipe->recipe_id == $rid) {
                continue;
            }

            $recipe = Recipe::find($rid);

            $cal = 0;
            $prot = 0;
            $fat = 0;
            //$ch = 0;
            foreach ($existingRecipe->foodstuffs as &$foodstuff) {
                $f = Foodstuff::where('id', $foodstuff->foodstuff_id)->get()[0];
                $cal += $foodstuff->amount * ($f->calories / 100);
                $prot += $foodstuff->amount * ($f->proteins / 100);
                $fat += $foodstuff->amount * ($f->fats / 100);
                //$ch += $foodstuff->amount * ($f->carbohydrates / 100);
            }

            $rCal = 0;
            $rProt = 0;
            $rFat = 0;
            $rCh = 0;

            $holders = [];
            foreach ($recipe->foodstuffs as $fm) {
                $f = RecipeFoodstuff::where('foodstuff_id', $fm->id)
                    ->where('recipe_id', $recipe->id)
                    ->get()[0];
                if ($f->proteins_holder == 0 && $f->fats_holder == 0 && $f->carbohydrates_holder == 0) {
                    $rCal += $f->amount * ($fm->calories / 100);
                    $rProt += $f->amount * ($fm->proteins / 100);
                    $rFat += $f->amount * ($fm->fats / 100);
                    //$rCh += $f->amount * ($fm->carbohydrates / 100);
                } else {
                    $holders[] = $fm;
                }
            }

            if (count($holders) > 0) {
                $rCalMin = $rProtMin = $rFatMin = $rChMin = 0;
                $rCalMax = $rProtMax = $rFatMax = $rChMax = 0;

                foreach ($holders as $h) {
                    $rCalMin += $h->min * ($h->calories / 100);
                    $rProtMin += $h->min * ($h->proteins / 100);
                    $rFatMin += $h->min * ($h->fats / 100);
                    //$rChMin += $h->min * ($h->carbohydrates / 100);

                    $rCalMax += $h->max * ($h->calories / 100);
                    $rProtMax += $h->max * ($h->proteins / 100);
                    $rFatMax += $h->max * ($h->fats / 100);
                    //$rChMax += $h->max * ($h->carbohydrates / 100);
                }

                $combinations[] = [
                    'caloriesMin' => $rCal + $rCalMin,
                    'proteinsMin' => $rProt + $rProtMin,
                    'fatsMin' => $rFat + $rFatMin,
                    //'carbohydratesMin' => $rCh + $rChMin,
                    'caloriesMax' => $rCal + $rCalMax,
                    'proteinsMax' => $rProt + $rProtMax,
                    'fatsMax' => $rFat + $rFatMax,
                    //'carbohydratesMax' => $rCh + $rChMax,
                    'recipe' => $recipe->id,
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

            $usefullCombinations = [];

            foreach ($combinations as $combination) {
                if ($combination['caloriesMin'] <= $cal && $combination['caloriesMax'] >= $cal
                    && $combination['proteinsMin'] <= $prot && $combination['proteinsMax'] >= $prot
                    && $combination['fatsMin'] <= $fat && $combination['fatsMax'] >= $fat) {
                    //&& $combination['carbohydratesMin'] <= $ch && $combination['carbohydratesMax'] >= $ch) {
                    $usefullCombinations[] = $combination;
                }

            }

            $targetRecipe = $recipe;

            $fixCal = 0;
            $fixProt = 0;
            $fixFat = 0;
            //$fixCh = 0;
            $newHolders = [];
            foreach ($targetRecipe->foodstuffs as $fm) {
                $f = RecipeFoodstuff::where('foodstuff_id', $fm->id)
                    ->where('recipe_id', $targetRecipe->id)
                    ->get()[0];
                if($f->proteins_holder == 0 && $f->fats_holder == 0 && $f->carbohydrates_holder == 0) {
                    $fixCal += $f->amount * ($fm->calories / 100);
                    $fixProt += $f->amount * ($fm->proteins / 100);
                    $fixFat += $f->amount * ($fm->fats / 100);
                    //$fixCh += $f->amount * ($fm->carbohydrates / 100);
                } else {
                    $newHolders[] = $fm;
                }
            }

            $newCombinations = [];

            if(count($newHolders) == 1) {
                $step = $newHolders[0]->step?? $newHolders[0]->min;
                for($i = $newHolders[0]->min; $i <= $newHolders[0]->max; $i += $step) {
                    $newCombinations[] = [
                        'calories' => $i * ($newHolders[0]->calories / 100) + $fixCal,
                        'proteins' => $i * ($newHolders[0]->proteins / 100) + $fixProt,
                        'fats' => $i * ($newHolders[0]->fats / 100) + $fixFat,
                        //'carbohydrates' => $i * ($newHolders[0]->carbohydrates / 100) + $fixCh,
                        'foodstuff_id' => $newHolders[0]->id,
                        'amounts' => $i,
                        'recipe_id' => $targetRecipe->id
                    ];
                }
            } else if(count($newHolders) == 2) {
                $step = $newHolders[0]->step?? $newHolders[0]->min;
                $step2 = $newHolders[1]->step?? $newHolders[1]->min;
                for($i = $newHolders[0]->min; $i <= $newHolders[0]->max; $i += $step) {
                    for ($j = $newHolders[1]->min; $j <= $newHolders[1]->max; $j += $step2) {
                        $newCombinations[] = [
                            'calories' => $i * ($newHolders[0]->calories / 100) + $j * ($newHolders[1]->calories / 100) + $fixCal,
                            'proteins' => $i * ($newHolders[0]->proteins / 100) + $j * ($newHolders[1]->proteins / 100) + $fixProt,
                            'fats' => $i * ($newHolders[0]->fats / 100) + $j * ($newHolders[1]->fats / 100) + $fixFat,
                            //'carbohydrates' => $i * ($newHolders[0]->carbohydrates / 100) + $j * ($newHolders[1]->carbohydrates / 100) + $fixCh,
                            'foodstuff_id' => $newHolders[0]->id . '-' . $newHolders[1]->id,
                            'amounts' => $i . '-' . $j,
                            'recipe_id' => $targetRecipe->id
                        ];
                    }
                }
            } else if(count($newHolders) == 3) {
                $step = $newHolders[0]->step?? $newHolders[0]->min;
                $step2 = $newHolders[1]->step?? $newHolders[1]->min;
                $step3 = $newHolders[2]->step?? $newHolders[2]->min;
                for($i = $newHolders[0]->min; $i <= $newHolders[0]->max; $i += $step) {
                    for ($j = $newHolders[1]->min; $j <= $newHolders[1]->max; $j += $step2) {
                        for ($k = $newHolders[2]->min; $k <= $newHolders[2]->max; $k += $step3) {
                            $newCombinations[] = [
                                'calories' => $i * ($newHolders[0]->calories / 100) + $j * ($newHolders[1]->calories / 100) + $k * ($newHolders[2]->calories / 100) + $fixCal,
                                'proteins' => $i * ($newHolders[0]->proteins / 100) + $j * ($newHolders[1]->proteins / 100) + $k * ($newHolders[2]->proteins / 100) + $fixProt,
                                'fats' => $i * ($newHolders[0]->fats / 100) + $j * ($newHolders[1]->fats / 100) + $k * ($newHolders[2]->fats / 100) + $fixFat,
                                //'carbohydrates' => $i * ($newHolders[0]->carbohydrates / 100) + $j * ($newHolders[1]->carbohydrates / 100) + $k * ($newHolders[2]->carbohydrates / 100) + $fixCh,
                                'foodstuff_id' => $newHolders[0]->id . '-' . $newHolders[1]->id . '-' . $newHolders[2]->id,
                                'amounts' => $i . '-' . $j . '-' . $k,
                                'recipe_id' => $targetRecipe->id
                            ];
                        }
                    }
                }
            } else {
                $newCombinations[] = [
                    'calories' => $fixCal,
                    'proteins' => $fixProt,
                    'fats' => $fixFat,
                    //'carbohydrates' => $fixCh,
                    'foodstuff_id' => '',
                    'amounts' => '',
                    'recipe_id' => $targetRecipe->id
                ];
            }
            //dd($newCombinations);

            $best     = null;
            $minDist2 = PHP_INT_MAX;  // čuvamo najmanju kvadratnu distancu

            foreach ($newCombinations as $cand) {
                $dCal  = $cand['calories']      - $cal;
                $dProt = $cand['proteins']      - $prot;
                $dFat  = $cand['fats']          - $fat;
                //$dCh   = $cand['carbohydrates'] - $ch;

                // kvadrat Euklidske distance (bez sqrt jer nam dovoljna komparacija)
                $dist2 = $dCal*$dCal
                    + $dProt*$dProt
                    + $dFat*$dFat;
                    //+ $dCh*$dCh;

                if ($dist2 < $minDist2) {
                    $minDist2 = $dist2;
                    $best     = $cand;
                }
            }

            $newHs = explode('-', $best['foodstuff_id']);
            $newH = array_map('intval', $newHs);
            $newAs = explode('-', $best['amounts']);
            $newA = array_map('intval', $newAs);
            $newRecipe = Recipe::find($best['recipe_id']);

            $newUserRecipe = UserRecipe::create([
                'user_id' => $user->id,
                'recipe_id' => $newRecipe->id,
                'status' => 'active',
                'type' => $t,
                'date' => $date
            ]);

            $existingRecipe->status = 'replaced';
            $existingRecipe->save();

            $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($newRecipe->id);
            foreach ($foodstuffs as $fn) {
                if($fn->proteins_holder == 0 && $fn->fats_holder == 0 && $fn->carbohydrates_holder == 0) {
                    UserRecipeFoodstuff::create([
                        'user_recipe_id' => $newUserRecipe->id,
                        'foodstuff_id' => $fn->foodstuff_id,
                        'amount' => $fn->amount,
                        'purchased' => 0
                    ]);
                }
            }

            for($i = 0; $i < count($newH); $i++) {
                UserRecipeFoodstuff::create([
                    'user_recipe_id' => $newUserRecipe->id,
                    'foodstuff_id' => $newH[$i],
                    'amount' => $newA[$i],
                    'purchased' => 0
                ]);
            }
        }


        return response()->json('success', 200);
    }

    public function repeatSpecificMeal(Request $request)
    {
        // 1. Brza provera autentifikacije
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->first();
        if (!$user) return response()->json(['error' => 'User not found'], 404);

        // 2. Preuzimanje parametara iz zahteva
        $targetDate   = $request->date;      // Tačan datum zamene
        $oldUserRecipeId = $request->oldUserMealId; // "Žrtva" - UserRecipe ID
        $newRecipeId  = $request->newRecipeId;  // Raw Recipe ID koji ubacujemo

        // 3. Dohvatanje "žrtve" i računanje njenih nutritivnih vrednosti
        $existingRecipe = UserRecipe::with('foodstuffs.foodstuff')->find($oldUserRecipeId);

        if (!$existingRecipe) {
            return response()->json(['error' => 'Target meal not found'], 404);
        }

        $targetCal = 0;
        $targetProt = 0;
        $targetFat = 0;

        foreach ($existingRecipe->foodstuffs as $userFood) {
            $f = $userFood->foodstuff;
            $targetCal  += $userFood->amount * ($f->calories / 100);
            $targetProt += $userFood->amount * ($f->proteins / 100);
            $targetFat  += $userFood->amount * ($f->fats / 100);
        }

        // 4. Priprema novog recepta i njegovih "holdera"
        $newRecipe = Recipe::with('foodstuffs')->find($newRecipeId);
        if (!$newRecipe) return response()->json(['error' => 'New recipe not found'], 404);

        $fixCal = 0; $fixProt = 0; $fixFat = 0;
        $holders = [];

        foreach ($newRecipe->foodstuffs as $fm) {
            // Koristimo pivot tabelu ili relaciju da proverimo da li je holder
            $pivot = RecipeFoodstuff::where('foodstuff_id', $fm->id)
                ->where('recipe_id', $newRecipe->id)
                ->first();

            if ($pivot->proteins_holder == 0 && $pivot->fats_holder == 0 && $pivot->carbohydrates_holder == 0) {
                $fixCal  += $pivot->amount * ($fm->calories / 100);
                $fixProt += $pivot->amount * ($fm->proteins / 100);
                $fixFat  += $pivot->amount * ($fm->fats / 100);
            } else {
                // Dodajemo pivot podatke u holder objekte radi lakšeg loop-a
                $fm->min = $pivot->min;
                $fm->max = $pivot->max;
                $fm->step = $pivot->step ?? $pivot->min;
                $holders[] = $fm;
            }
        }

        // 5. Generisanje kombinacija (Brute-force optimizacija)
        $combinations = [];
        $hCount = count($holders);

        if ($hCount == 0) {
            $combinations[] = ['calories' => $fixCal, 'proteins' => $fixProt, 'fats' => $fixFat, 'ids' => [], 'amounts' => []];
        } elseif ($hCount == 1) {
            for ($i = $holders[0]->min; $i <= $holders[0]->max; $i += $holders[0]->step) {
                $combinations[] = [
                    'calories' => $i * ($holders[0]->calories / 100) + $fixCal,
                    'proteins' => $i * ($holders[0]->proteins / 100) + $fixProt,
                    'fats'     => $i * ($holders[0]->fats / 100) + $fixFat,
                    'ids'      => [$holders[0]->id],
                    'amounts'  => [$i]
                ];
            }
        } elseif ($hCount == 2) {
            for ($i = $holders[0]->min; $i <= $holders[0]->max; $i += $holders[0]->step) {
                for ($j = $holders[1]->min; $j <= $holders[1]->max; $j += $holders[1]->step) {
                    $combinations[] = [
                        'calories' => ($i * $holders[0]->calories + $j * $holders[1]->calories) / 100 + $fixCal,
                        'proteins' => ($i * $holders[0]->proteins + $j * $holders[1]->proteins) / 100 + $fixProt,
                        'fats'     => ($i * $holders[0]->fats + $j * $holders[1]->fats) / 100 + $fixFat,
                        'ids'      => [$holders[0]->id, $holders[1]->id],
                        'amounts'  => [$i, $j]
                    ];
                }
            }
        } elseif ($hCount == 3) {
            for ($i = $holders[0]->min; $i <= $holders[0]->max; $i += $holders[0]->step) {
                for ($j = $holders[1]->min; $j <= $holders[1]->max; $j += $holders[1]->step) {
                    for ($k = $holders[2]->min; $k <= $holders[2]->max; $k += $holders[2]->step) {
                        $combinations[] = [
                            'calories' => ($i * $holders[0]->calories + $j * $holders[1]->calories + $k * $holders[2]->calories) / 100 + $fixCal,
                            'proteins' => ($i * $holders[0]->proteins + $j * $holders[1]->proteins + $k * $holders[2]->proteins) / 100 + $fixProt,
                            'fats'     => ($i * $holders[0]->fats + $j * $holders[1]->fats + $k * $holders[2]->fats) / 100 + $fixFat,
                            'ids'      => [$holders[0]->id, $holders[1]->id, $holders[2]->id],
                            'amounts'  => [$i, $j, $k]
                        ];
                    }
                }
            }
        }

        // 6. Pronalaženje najbolje opcije (Minimalna kvadratna distanca)
        $best = null;
        $minDist2 = PHP_INT_MAX;

        foreach ($combinations as $cand) {
            $dCal  = $cand['calories'] - $targetCal;
            $dProt = $cand['proteins'] - $targetProt;
            $dFat  = $cand['fats']     - $targetFat;

            $dist2 = ($dCal * $dCal) + ($dProt * $dProt) + ($dFat * $dFat);

            if ($dist2 < $minDist2) {
                $minDist2 = $dist2;
                $best = $cand;
            }
        }

        // 7. DB Transakcija za sigurnost (opciono ali preporučljivo)
        \DB::transaction(function () use ($user, $newRecipe, $existingRecipe, $targetDate, $best) {
            // Kreiranje novog UserRecipe-a
            $newUserRecipe = UserRecipe::create([
                'user_id'   => $user->id,
                'recipe_id' => $newRecipe->id,
                'status'    => 'active',
                'type'      => $existingRecipe->type,
                'date'      => $targetDate
            ]);

            // Arhiviranje starog
            $existingRecipe->update(['status' => 'replaced']);

            // Upis fiksnih namirnica
            $allRecipeFoodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($newRecipe->id);
            foreach ($allRecipeFoodstuffs as $fn) {
                if ($fn->proteins_holder == 0 && $fn->fats_holder == 0 && $fn->carbohydrates_holder == 0) {
                    UserRecipeFoodstuff::create([
                        'user_recipe_id' => $newUserRecipe->id,
                        'foodstuff_id'   => $fn->foodstuff_id,
                        'amount'         => $fn->amount,
                        'purchased'      => 0
                    ]);
                }
            }

            // Upis optimizovanih holdera
            foreach ($best['ids'] as $index => $foodstuffId) {
                UserRecipeFoodstuff::create([
                    'user_recipe_id' => $newUserRecipe->id,
                    'foodstuff_id'   => $foodstuffId,
                    'amount'         => $best['amounts'][$index],
                    'purchased'      => 0
                ]);
            }
        });

        return response()->json(['message' => 'Meal replaced successfully'], 200);
    }

    public function getSubscribers() {
        return Subscriber::all()->count();
    }

    public function addSubscriber(Request $request) {
        return Subscriber::create([
            'name' => $request->subscriberName,
            'email' => $request->subscriberEmail
        ]);
    }

    public function updateNotificationToken(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();
        $user->notification_token = $request->input('notificationToken');
        $user->save();

        Log::error('Notification tokens: ' . json_encode($request->all()));

        return response()->json('success', 200);
    }

    public function updateNotificationStatus(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();
        $user->notification_status = $request->notificationStatus;
        $user->save();

        return response()->json('success', 200);
    }

    public function showNotificationTest() {
        return view('notification-test');
    }

    public function getUserRecipeAlternatives(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $alternativesRaw = $this->recipeService->getRecipeAlternatives(UserRecipe::find($request->recipeId));

        $recipes = [];
        $i = 0;
        foreach ($alternativesRaw['combinations'] as $recipe) {
            $r = Recipe::find($recipe['recipe']);
            if ($r->bookmarked_status == 1) {
                $r->bookmarked_status = 'bookmarked';
            } else if ($r->bookmarked_status == -1) {
                $r->bookmarked_status = 'deleted';
            } else {
                $r->bookmarked_status = 'active';
            }
            $recipes[] = $r;
            $i++;
            if($i > 9) break;
        }

        return response()->json($recipes);
    }

    public function changeUserRecipeAlternative(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();

        $userRecipe = UserRecipe::find($request->userRecipeId);
        $userRecipe->status = 'replaced';
        $userRecipe->save();

        $data = $this->recipeService->getRecipeAlternatives($userRecipe);

        $usefullCombinations = $data['combinations'];
        $cal = $data['cal'];
        $prot = $data['prot'];
        $fat = $data['fat'];
        //$ch = $data['ch'];
        $targetRecipe = null;

        foreach ($usefullCombinations as $recipe) {
            if($recipe['recipe'] == $request->recipeId) {
                $targetRecipe = Recipe::find($recipe['recipe']);;
            }
        }

        $fixCal = 0;
        $fixProt = 0;
        $fixFat = 0;
        //$fixCh = 0;
        $newHolders = [];
        foreach ($targetRecipe->foodstuffs as $fm) {
            $f = RecipeFoodstuff::where('foodstuff_id', $fm->id)
                ->where('recipe_id', $targetRecipe->id)
                ->get()[0];
            if($f->proteins_holder == 0 && $f->fats_holder == 0 && $f->carbohydrates_holder == 0) {
                $fixCal += $f->amount * ($fm->calories / 100);
                $fixProt += $f->amount * ($fm->proteins / 100);
                $fixFat += $f->amount * ($fm->fats / 100);
                //$fixCh += $f->amount * ($fm->carbohydrates / 100);
            } else {
                $newHolders[] = $fm;
            }
        }

        $newCombinations = [];

        if(count($newHolders) == 1) {
            $step = $newHolders[0]->step?? $newHolders[0]->min;
            for($i = $newHolders[0]->min; $i <= $newHolders[0]->max; $i += $step) {
                $newCombinations[] = [
                    'calories' => $i * ($newHolders[0]->calories / 100) + $fixCal,
                    'proteins' => $i * ($newHolders[0]->proteins / 100) + $fixProt,
                    'fats' => $i * ($newHolders[0]->fats / 100) + $fixFat,
                    //'carbohydrates' => $i * ($newHolders[0]->carbohydrates / 100) + $fixCh,
                    'foodstuff_id' => $newHolders[0]->id,
                    'amounts' => $i,
                    'recipe_id' => $targetRecipe->id
                ];
            }
        } else if(count($newHolders) == 2) {
            $step = $newHolders[0]->step?? $newHolders[0]->min;
            $step2 = $newHolders[1]->step?? $newHolders[1]->min;
            for($i = $newHolders[0]->min; $i <= $newHolders[0]->max; $i += $step) {
                for ($j = $newHolders[1]->min; $j <= $newHolders[1]->max; $j += $step2) {
                    $newCombinations[] = [
                        'calories' => $i * ($newHolders[0]->calories / 100) + $j * ($newHolders[1]->calories / 100) + $fixCal,
                        'proteins' => $i * ($newHolders[0]->proteins / 100) + $j * ($newHolders[1]->proteins / 100) + $fixProt,
                        'fats' => $i * ($newHolders[0]->fats / 100) + $j * ($newHolders[1]->fats / 100) + $fixFat,
                        //'carbohydrates' => $i * ($newHolders[0]->carbohydrates / 100) + $j * ($newHolders[1]->carbohydrates / 100) + $fixCh,
                        'foodstuff_id' => $newHolders[0]->id . '-' . $newHolders[1]->id,
                        'amounts' => $i . '-' . $j,
                        'recipe_id' => $targetRecipe->id
                    ];
                }
            }
        } else if(count($newHolders) == 3) {
            $step = $newHolders[0]->step?? $newHolders[0]->min;
            $step2 = $newHolders[1]->step?? $newHolders[1]->min;
            $step3 = $newHolders[2]->step?? $newHolders[2]->min;
            for($i = $newHolders[0]->min; $i <= $newHolders[0]->max; $i += $step) {
                for ($j = $newHolders[1]->min; $j <= $newHolders[1]->max; $j += $step2) {
                    for ($k = $newHolders[2]->min; $k <= $newHolders[2]->max; $k += $step3) {
                        $newCombinations[] = [
                            'calories' => $i * ($newHolders[0]->calories / 100) + $j * ($newHolders[1]->calories / 100) + $k * ($newHolders[2]->calories / 100) + $fixCal,
                            'proteins' => $i * ($newHolders[0]->proteins / 100) + $j * ($newHolders[1]->proteins / 100) + $k * ($newHolders[2]->proteins / 100) + $fixProt,
                            'fats' => $i * ($newHolders[0]->fats / 100) + $j * ($newHolders[1]->fats / 100) + $k * ($newHolders[2]->fats / 100) + $fixFat,
                            //'carbohydrates' => $i * ($newHolders[0]->carbohydrates / 100) + $j * ($newHolders[1]->carbohydrates / 100) + $k * ($newHolders[2]->carbohydrates / 100) + $fixCh,
                            'foodstuff_id' => $newHolders[0]->id . '-' . $newHolders[1]->id . '-' . $newHolders[2]->id,
                            'amounts' => $i . '-' . $j . '-' . $k,
                            'recipe_id' => $targetRecipe->id
                        ];
                    }
                }
            }
        } else {
            $newCombinations[] = [
                'calories' => $fixCal,
                'proteins' => $fixProt,
                'fats' => $fixFat,
                //'carbohydrates' => $fixCh,
                'foodstuff_id' => '',
                'amounts' => '',
                'recipe_id' => $targetRecipe->id
            ];
        }

        $best     = null;
        $minDist2 = PHP_INT_MAX;  // čuvamo najmanju kvadratnu distancu

        foreach ($newCombinations as $cand) {
            $dCal  = $cand['calories']      - $cal;
            $dProt = $cand['proteins']      - $prot;
            $dFat  = $cand['fats']          - $fat;
            //$dCh   = $cand['carbohydrates'] - $ch;

            // kvadrat Euklidske distance (bez sqrt jer nam dovoljna komparacija)
            $dist2 = $dCal*$dCal
                + $dProt*$dProt
                + $dFat*$dFat;
                //+ $dCh*$dCh;

            if ($dist2 < $minDist2) {
                $minDist2 = $dist2;
                $best     = $cand;
            }
        }

        $newHs = explode('-', $best['foodstuff_id']);
        $newH = array_map('intval', $newHs);
        $newAs = explode('-', $best['amounts']);
        $newA = array_map('intval', $newAs);
        $newRecipe = Recipe::find($best['recipe_id']);

        $newUserRecipe = UserRecipe::create([
            'user_id' => $user->id,
            'recipe_id' => $newRecipe->id,
            'status' => 'active',
            'type' => $userRecipe->type,
            'date' => $userRecipe->date
        ]);

        $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($newRecipe->id);
        foreach ($foodstuffs as $fn) {
            if($fn->proteins_holder == 0 && $fn->fats_holder == 0 && $fn->carbohydrates_holder == 0) {
                UserRecipeFoodstuff::create([
                    'user_recipe_id' => $newUserRecipe->id,
                    'foodstuff_id' => $fn->foodstuff_id,
                    'amount' => $fn->amount,
                    'purchased' => 0
                ]);
            }
        }

        for($i = 0; $i < count($newH); $i++) {
            UserRecipeFoodstuff::create([
                'user_recipe_id' => $newUserRecipe->id,
                'foodstuff_id' => $newH[$i],
                'amount' => $newA[$i],
                'purchased' => 0
            ]);
        }

        return response()->json($recipe);
    }



    function decodeSchedule(array $schedule): array {
        $DAY  = ['Pon','Uto','Sre','Cet','Pet','Sub','Ned'];
        $MEAL = [1, 2, 4, 3, 3];

        $chainsLabeled = [];
        $edges = [];

        foreach ($schedule as $chainIdx => $chain) {
            $labels = [];
            foreach ($chain as $node) {
                $x = $node['coordinates']['x'];
                $y = $node['coordinates']['y'];
                $meal = $MEAL[$x] ?? "row{$x}";
                $day  = $DAY[$y]  ?? "col{$y}";
                $labels[] = "{$meal}–{$day}";
            }
            $chainsLabeled[] = $labels;

            $edgeList = [];
            for ($i=0; $i<count($labels)-1; $i++) {
                $edgeList[] = "{$labels[$i]} ⇄ {$labels[$i+1]}";
            }
            $edges[] = $edgeList;
        }
        return ['chainsLabeled' => $chainsLabeled, 'edges' => $edges];
    }

    public function validateSubscription(Request $request)
    {
        $platform = $request->input('platform'); // 'android' or 'ios'
        $token = $request->input('purchase_token'); // from Flutter

        if ($platform === 'android') {
            return $this->validateAndroid($token, $request->input('product_id'));
        } elseif ($platform === 'ios') {
            return $this->validateIOS($token);
        }

        return response()->json(['error' => 'Invalid platform'], 400);
    }

    private function validateAndroid($purchaseToken, $productId)
    {
        $packageName = config('services.google.package_name');

        // Authenticate with service account JSON
        $client = new GoogleClient();
        $client->setAuthConfig(base_path('fity-billing-service-account.json'));
        $client->addScope('https://www.googleapis.com/auth/androidpublisher');

        $service = new \Google\Service\AndroidPublisher($client);

        try {
            $result = $service->purchases_subscriptions->get(
                $packageName,
                $productId,
                $purchaseToken
            );

            $isActive = $result->getExpiryTimeMillis() > now()->valueOf();

            return response()->json([
                'is_active'   => $isActive,
                'expiry_date' => $result->getExpiryTimeMillis(),
            ]);
        } catch (\Exception $e) {
            Log::error('ERROR:' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function validateIOS($receiptData)
    {
        $endpoint = env('APPLE_SUBSCRIPTION_VALIDATION_URL');
        // Use production endpoint in prod: https://buy.itunes.apple.com/verifyReceipt

//        $jwt = $receiptData;
//        $base64Token = base64_encode($jwt);

        $response = Http::withOptions([
            'connect_timeout' => 120,   // koliko sekundi čekamo da uspostavimo konekciju
            'timeout' => 120,          // ukupno vreme čekanja za odgovor
        ])->post($endpoint, [
            'receipt-data' => $receiptData,
            'password'     => config('services.apple.shared_secret'),
            'exclude-old-transactions' => true,
        ]);

//        Log::error('Parameters: ' . $base64Token . ' ' . config('services.apple.shared_secret'));

        if (!$response->ok()) {
            Log::error('ERROR: Apple validation failed');
            return response()->json(['error' => 'Apple validation failed'], 400);
        }

        Log::error('Sandbox: ' . json_encode($response->json(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $data = $response->json();

        // Parse Apple receipt fields
        $latestReceipt = collect($data['latest_receipt_info'] ?? [])->last();

        $expiryDate = isset($latestReceipt['expires_date_ms'])
            ? intval($latestReceipt['expires_date_ms'])
            : null;

        $isActive = $expiryDate && $expiryDate > now()->valueOf();

        return response()->json([
            'is_active'   => $isActive,
            'expiry_date' => $expiryDate,
        ]);
    }

    public function getLastUserSchedule(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();

        $lastUserSchedule = UserSchedule::latest('created_at')
            ->where('user_id', $user->id)
            ->first();

        return response()->json($lastUserSchedule);
    }

    public function deleteUser(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();

        $user->delete();

        return response()->json('success', 200);
    }

    public function updateSubscription(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();
        $user->is_subscribed = $request->isSubscribed;
        $user->save();

        if($user->is_subscribed == 1) {
            $customFields = [
                'subscription_type' => 'Monthly'
            ];
            $this->userService->updateSubscriberFields($user->email, $customFields);
        }

        return response()->json('success', 200);
    }

    public function undoWater(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();
        $lastUserWater = UserWater::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();
        if($lastUserWater) {
            $lastUserWater->delete();
        }

        return response()->json('success', 200);
    }

    public function addRecipeToPlan(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->get()->first();

        return response()->json('success', 200);
    }

    public function sendNotificationTest() {
        $user = User::find(576);

        // Samo pozoveš funkciju iz servisa
        $result = $this->authService->sendNotification(
            $user->notification_token,
            'Pozdrav ' . $user->name,
            'Vaša obaveštenja su ažurirana.'
        );

        if ($result) {
            return response()->json('success', 200);
        } else {
            return response()->json('error sending message', 500);
        }
    }

    public function testCustomFields() {
        $email = 'rtanglestudio@gmail.com';
        $customFields = [
            'member_type' => 'Regular',
            'gender' => 'Female',
            'subscription_status' => 'Expired',
            'subscription_type' => 'Yearly'
        ];
        $this->userService->updateSubscriberFields($email, $customFields);
        return response()->json('success', 200);
    }

    public function showUsersStatistics() {
        $users = User::where('id', '>', 570)->where('is_test', 0)->get();
        $total = $users->count();
        $subscribed = 0;
        $phantom = 0;

        foreach($users as $user) {
            if($user->is_subscribed == 1) {
                $subscribed++;
            }

            if($user->landed == 0) {
                $phantom++;
            }
        }

        return view('users-statistics', compact('total', 'subscribed', 'phantom'));
    }

    public function exportPdf($id)
    {
        $user = User::find($id);
//        if($user->macros_type == '1') {
//            $target = $this->userService->getMacrosForUser($user);
//        } else {
        $target = $this->userService->getMacrosForUser2($user);
//        }

        $userAllergies = UserAllergy::where('user_id', $id)->get();
        $allergyIds = [];
        foreach ($userAllergies as $userAllergy) {
//            if(Foodstuff::where('id', $userAllergy->foodstuff_id)->get()->first()->foodstuff_category_id == 6) continue;
            $allergyIds[] = $userAllergy->foodstuff_id;
        }

        $response = Http::timeout(10000)
            ->withoutVerifying()
            ->post('https://algo.getfity.app/meal-plan', [
                'target_calories' => $target['calories'],
                'target_protein' => $target['proteins'],
                'target_fat' => $target['fats'],
                'meals_num' => $user->meals_num,
                'tolerance_calories' => $user->tolerance_calories,
                'tolerance_proteins' => $user->tolerance_proteins,
                'tolerance_fats' => $user->tolerance_fats,
                'days' => 30,
                'allergy_holder_ids' => $allergyIds
            ]);


        $data = $response->json();

        $i = 0;
//        for($k = 0; $k < 5; $k++) {
            foreach ($data['daily_plans'] as $day) {
                if (!$day['exists']) continue;
                $date = date('Y-m-d', strtotime('+' . $i . ' days'));
                $i++;
                $lunch = false;
                foreach ($day['meals'] as $meal) {
//                if($meal['same_meal_id'] == 33) {
//                    continue;
//                }
                    $r = Recipe::find($meal['same_meal_id']);
//                    $userRecipe = UserRecipe::create([
//                        'user_id' => $userId,
//                        'recipe_id' => $meal['same_meal_id'],
//                        'status' => 'active',
//                        'date' => $date,
//                        'type' => $lunch && $r->type == 2 ? 4 : $r->type
//                    ]);
                    if ($r->type == 2) {
                        $lunch = true;
                    }
                    $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($meal['same_meal_id']);
                    foreach ($foodstuffs as $foodstuff) {
                        if ($foodstuff->proteins_holder == 0 && $foodstuff->fats_holder == 0 && $foodstuff->carbohydrates_holder == 0) {
//                            UserRecipeFoodstuff::create([
//                                'user_recipe_id' => $userRecipe->id,
//                                'foodstuff_id' => $foodstuff->foodstuff_id,
//                                'amount' => $foodstuff->amount,
//                                'purchased' => 0
//                            ]);
                        }
                    }

                    foreach ($meal['holder_quantities'] as $key => $holder) {
//                        UserRecipeFoodstuff::create([
//                            'user_recipe_id' => $userRecipe->id,
//                            'foodstuff_id' => $key,
//                            'amount' => $holder,
//                            'purchased' => 0
//                        ]);
                    }
                }
            }
//        }

        foreach($data['daily_plans'] as &$day) {
            $dayCalories = $dayProteins = $dayFats = $dayCarbs = 0;
            foreach($day['meals'] as &$meal) {
                $meal['carbohydrates'] = 0;
                $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($meal['same_meal_id']);
                foreach ($meal['holder_quantities'] as $key => $holder) {
                    $f = Foodstuff::find($key);
                    $meal['carbohydrates'] += $f->carbohydrates * $holder / 100;
                }
                foreach ($foodstuffs as $foodstuff) {
                    if($foodstuff->proteins_holder == 0 && $foodstuff->fats_holder == 0 && $foodstuff->carbohydrates_holder == 0) {
                        $f = Foodstuff::find($foodstuff->foodstuff_id);
                        $meal['carbohydrates'] += $f->carbohydrates * $foodstuff->amount / 100;
                    }
                }
                $meal['foodstuffs'] = $foodstuffs;
                $dayCalories += $meal['calories'];
                $dayProteins += $meal['proteins'];
                $dayFats += $meal['fats'];

            }
            $day['calories'] = $dayCalories;
            $day['proteins'] = $dayProteins;
            $day['fats'] = $dayFats;
        }

        $pdf = PDF::loadView('pdf.user-meal-plan', [
            'user' => $user,
            'data' => $data
        ]);

        return $pdf->download('meal-plan-user-'.$user->id.'.pdf');
    }


}
