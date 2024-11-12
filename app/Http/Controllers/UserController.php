<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
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

        $response = Http::timeout(10000)->post('http://127.0.0.1:8000/meal-plan', [
            'target_calories' => $calories,
            'target_protein' => $proteins,
            'target_fat' => $fats,
            'meals_num' => $user->meals_num,
            'tolerance_calories' => $user->tolerance_calories,
            'tolerance_proteins' => $user->tolerance_proteins,
            'tolerance_fats' => $user->tolerance_fats,
        ]);

        if ($response->successful()) {
            $data = $response->json();
        } else {
            $error = $response->body();
            echo $error;
        }

        dd($data);

        return view('user-recipes', compact('user', 'target', 'data'));

    }

}
