<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct() {
        $this->userService = new UserService();
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
            'insulin_resistance' => $request->input('insulin_resistance')
        ];

        $user = $this->userService->addUser($userData);
        return redirect()->route('assign-recipes-to-user', ['userId' => $user->id]);
    }

    public function assignRecipesToUser($userId) {
        $user = User::find($userId);
        $calories = 0;
        $weight = 0;
        if($user->gender == 'm') {
            $calories = 66.47 + (13.75 * $user->weight) + (5.003 * $user->height) - (6.755 * $user->age);
            $weight = 48 + (1.1 * ($user->height - 152.4));
        } else {
            $calories = 655.1 + (9.563 * $user->weight) + (1.85 * $user->height) - (4.676 * $user->age);
            $weight = 45 + (0.9 * ($user->height - 152.4));
        }
        $calories = $calories * $user->activity;

        $proteins = 0;
        $fats = 0;
        if($user->goal == 'reduction') {
            $calories -= 300;
            $proteins = 2 * $weight;
            $fats = $weight;
        } else if($user->goal == 'increase') {
            $calories += 500;
            $proteins = 2 * $weight;
            $fats = 1.2 * $weight;
        } else {
            switch ($user->activity) {
                case '1.0':
                    $proteins = 1.6 * $weight;
                    $fats = $weight;
                    break;
                case '1.15':
                    $proteins = 1.8 * $weight;
                    $fats = $weight;
                    break;
                case '1.3':
                    $proteins = 2 * $weight;
                    $fats = $weight;
                    break;
                case '1.5':
                    $proteins = 2 * $weight;
                    $fats = 1.1 * $weight;
                    break;
                case '1.75':
                    $proteins = 2 * $weight;
                    $fats = 1.3 * $weight;
                    break;
                default:
                    break;
            }
        }

        $target = [
            'calories' => $calories,
            'proteins' => $proteins,
            'fats' => $fats,
        ];

        dd($target);

        $notApprovedCombination = [];

        $found = false;
        while(!$found) {
            $recipes = Recipe::with('foodstuffs')
            ->inRandomOrder()
            ->take(3)
            ->get()
            ->map(function($recipe) {
                $recipe->total_calories = $recipe->foodstuffs->sum(fn($fs) => $fs->calories * $fs->min / 100);
                $recipe->total_proteins = $recipe->foodstuffs->sum(fn($fs) => $fs->proteins * $fs->min / 100);
                $recipe->total_fats = $recipe->foodstuffs->sum(fn($fs) => $fs->fats * $fs->min / 100);
                $recipe->total_carbohydrates = $recipe->foodstuffs->sum(fn($fs) => $fs->carbohydrates * $fs->min / 100);
                return $recipe;
            });


            $combination = [$recipes[0]->id, $recipes[1]->id, $recipes[2]->id];

            if(!in_array($combination, $notApprovedCombination)) {

            }
        }

//        dd($target);

    }

}
