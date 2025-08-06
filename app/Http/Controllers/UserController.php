<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Models\Foodstuff;
use App\Models\FoodstuffCategory;
use App\Models\Photo;
use App\Models\Recipe;
use App\Models\RecipeFoodstuff;
use App\Models\Scope;
use App\Models\User;
use App\Models\UserAllergy;
use App\Models\UserRecipe;
use App\Models\UserRecipeFoodstuff;
use App\Models\UserWater;
use App\Models\UserWeight;
use App\Repositories\UserRecipeRepository;
use App\Services\AuthService;
use App\Services\PhotoService;
use App\Services\RecipeFoodstuffService;
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
use Illuminate\Support\Facades\Storage;
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
    protected ScopeService $scopeService;
    protected PhotoService $photoService;
    protected UserRecipeRepository $userRecipeRepository;
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
        ];

        $user = $this->userService->addUser($userData);
        //dd($user);
        return redirect()->route('assign-recipes-to-user', ['userId' => $user->id]);
    }

    public function assignRecipesToUser($userId) {

        $user = User::find($userId);
        if($user->macros_type == '1') {
            $target = $this->userService->getMacrosForUser($user);
        } else {
            $target = $this->userService->getMacrosForUser2($user);
        }

        $userAllergies = UserAllergy::where('user_id', $userId)->get();
        $allergyIds = [];
        foreach ($userAllergies as $userAllergy) {
            $allergyIds[] = $userAllergy->foodstuff_id;
        }

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
                'days' => 30,
                'allergy_holder_ids' => $allergyIds
            ]);


        $data = $response->json();

        $i = 0;
        foreach ($data['daily_plans'] as $day) {
            if(!$day['exists']) continue;
            $date = date('Y-m-d', strtotime('+' . $i . ' days'));
            $i++;
            $lunch = false;
            foreach ($day['meals'] as $meal) {
//                if($meal['same_meal_id'] == 33) {
//                    continue;
//                }
                $r = Recipe::find($meal['same_meal_id']);
//                $userRecipe = UserRecipe::create([
//                    'user_id' => $userId,
//                    'recipe_id' => $meal['same_meal_id'],
//                    'status' => 'active',
//                    'date' => $date,
//                    'type' => $lunch && $r->type == 2? 4: $r->type
//                ]);
                if($r->type == 2) {
                    $lunch = true;
                }
                $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($meal['same_meal_id']);
                foreach ($foodstuffs as $foodstuff) {
                    if($foodstuff->proteins_holder == 0 && $foodstuff->fats_holder == 0 && $foodstuff->calories_holder == 0) {
//                        UserRecipeFoodstuff::create([
//                            'user_recipe_id' => $userRecipe->id,
//                            'foodstuff_id' => $foodstuff->foodstuff_id,
//                            'amount' => $foodstuff->amount,
//                            'purchased' => 0
//                        ]);
                    }
                }

                foreach ($meal['holder_quantities'] as $key => $holder) {
//                    UserRecipeFoodstuff::create([
//                        'user_recipe_id' => $userRecipe->id,
//                        'foodstuff_id' => $key,
//                        'amount' => $holder,
//                        'purchased' => 0
//                    ]);
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

        $userWater = $query->first();
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
        $this->userService->assignFirebaseUid($request->userId, $firebaseUid, $request->email, $request->name);
        return response()->json(['user' => User::find($request->userId)]);
    }

    public function getRecipesByUserIdAndWeek(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $recipes = $this->userRecipeService->getUserRecipesByDate($userId, $request->startDate, $request->endDate);
        return response()->json($recipes);
    }

    public function getRecipeByUserIdAndRecipeId(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $recipe = $this->userRecipeService->getUserRecipeByUserIdAndRecipeId($request->input('recipeId'), $request->input('screen'));
        if ($recipe->bookmarked_status == 1) {
            $recipe->bookmarked_status = 'bookmarked';
        } else if ($recipe->bookmarked_status == -1) {
            $recipe->bookmarked_status = 'deleted';
        } else {
            $recipe->bookmarked_status = 'active';
        }
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
        $meals = ['dorucak, rucak, vecera'];
        if($user->meals_num > 3) {
            array_push($meals, 'uzina1');
        }
        if($user->meals_num > 4) {
            array_push($meals, 'uzina2');
        }
        $user->meals = $meals;
        return response()->json($user);
    }

    public function appleSignInCallback(Request $request) {
        $queryParams = http_build_query($request->all());
        $intentUrl = "intent://callback?' . $queryParams . '#Intent;package=rs.fity;scheme=signinwithapple;end";
        return redirect()->away($intentUrl);
    }

    public function addUserWeight(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if(!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userId = User::where('firebase_uid', $firebaseUid)->first()->id;
        $userWeight = UserWeight::create([
            'user_id' => $userId,
            'weight' => $request->input('weight'),
            ]);
        $userWeight->created_at = Carbon::parse($request->input('created_at'))->format('Y-m-d H:i:s');
        $userWeight->save();
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
//        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
//        if (!$firebaseUid) {
//            return response()->json(['error' => 'Unauthorized'], 401);
//        }
//
//        $user = User::where('firebase_uid', $firebaseUid)->first();
        $userRecipe = UserRecipe::find($request->input('recipeId'));
        $userRecipe->status = $request->input('status');
        $userRecipe->save();

        $cal = 0;
        $prot = 0;
        $fat = 0;
        $ch = 0;
        foreach ($userRecipe->foodstuffs as &$foodstuff) {
            $f = Foodstuff::where('id', $foodstuff->foodstuff_id)->get()[0];
            $cal += $foodstuff->amount * ($f->calories / 100);
            $prot += $foodstuff->amount * ($f->proteins / 100);
            $fat += $foodstuff->amount * ($f->fats / 100);
            $ch += $foodstuff->amount * ($f->carbohydrates / 100);
        }

        $type = $userRecipe->type == 4 ? 2: $userRecipe->type;
        $recipes = Recipe::where('type', $type)->get();
        $combinations = [];

        foreach ($recipes as $recipe) {
            $rCal = $cal;
            $rProt = $prot;
            $rFat = $fat;
            $rCh = $ch;

            $holders = [];
            foreach ($recipe->foodstuffs as $fm) {
                $f = RecipeFoodstuff::where('foodstuff_id', $fm->id)
                    ->where('recipe_id', $recipe->id)
                    ->get()[0];
                if($f->proteins_holder == 0 && $f->fats_holder == 0 && $f->carbohydrates_holder == 0) {
                    $rCal += $f->amount * ($fm->calories / 100);
                    $rProt += $f->amount * ($fm->proteins / 100);
                    $rFat += $f->amount * ($fm->fats / 100);
                    $rCh += $f->amount * ($fm->carbohydrates / 100);
                } else {
                    $holders[] = $fm;
                }

                if(count($holders) == 1) {
                    for($i = $holders[0]->min; $i <= $holders[0]->max; $i += $holders[0]->step) {
                        $rCal += $i * ($holders[0]->calories / 100);
                        $rProt += $i * ($holders[0]->proteins / 100);
                        $rFat += $i * ($holders[0]->fats / 100);
                        $rCh += $i * ($holders[0]->carbohydrates / 100);

                        $combinations[] = [
                            'calories' => $rCal,
                            'proteins' => $rProt,
                            'fats' => $rFat,
                            'carbohydrates' => $rCh,
                            'recipe' => $recipe->id
                        ];
                    }
                } else if(count($holders) == 2) {
                    for($i = $holders[0]->min; $i <= $holders[0]->max; $i += $holders[0]->step) {
                        for($j = $holders[1]->min; $j <= $holders[1]->max; $j += $holders[1]->step) {
                            $rCal += $i * ($holders[0]->calories / 100) + $j * ($holders[1]->calories / 100);
                            $rProt += $i * ($holders[0]->proteins / 100) + $j * ($holders[1]->proteins / 100);
                            $rFat += $i * ($holders[0]->fats / 100) + $j * ($holders[1]->fats / 100);
                            $rCh += $i * ($holders[0]->carbohydrates / 100) + $j * ($holders[1]->carbohydrates / 100);

                            $combinations[] = [
                                'calories' => $rCal,
                                'proteins' => $rProt,
                                'fats' => $rFat,
                                'carbohydrates' => $rCh,
                                'recipe' => $recipe->id
                            ];
                        }
                    }
                } else if(count($holders) == 3) {
                    for($i = $holders[0]->min; $i <= $holders[0]->max; $i += $holders[0]->step) {
                        for($j = $holders[1]->min; $j <= $holders[1]->max; $j += $holders[1]->step) {
                            for($k = $holders[2]->min; $k <= $holders[2]->max; $k += $holders[2]->step) {
                                $rCal += $i * ($holders[0]->calories / 100) + $j * ($holders[1]->calories / 100) + $k * ($holders[2]->calories / 100);
                                $rProt += $i * ($holders[0]->proteins / 100) + $j * ($holders[1]->proteins / 100) + $k * ($holders[2]->proteins / 100);
                                $rFat += $i * ($holders[0]->fats / 100) + $j * ($holders[1]->fats / 100) + $k * ($holders[2]->fats / 100);
                                $rCh += $i * ($holders[0]->carbohydrates / 100) + $j * ($holders[1]->carbohydrates / 100) + $k * ($holders[2]->carbohydrates / 100);

                                $combinations[] = [
                                    'calories' => $rCal,
                                    'proteins' => $rProt,
                                    'fats' => $rFat,
                                    'carbohydrates' => $rCh,
                                    'recipe' => $recipe->id
                                ];
                            }
                        }
                    }
                }
            }
        }

        dd($combinations);

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
        return response()->json('success', 200);
    }

    public function repeatMeals(Request $request) {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return response()->json('success', 200);
    }
}
