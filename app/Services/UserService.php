<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class UserService
{
    protected UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }
    public function addUser($userData) {
        return $this->userRepository->addUser($userData);
    }

    public function editUser($userData,$userId){
        return $this->userRepository->editUser($userData,$userId);
    }

    public function getMacrosForUser($user){

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
            switch ($user->activity) {
                case '1.2':
                    $proteins = 1.6 * $weight;
                    $fats = $weight;
                    break;
                case '1.375':
                    $proteins = 1.8 * $weight;
                    $fats = $weight;
                    break;
                case '1.55':
                    $proteins = 2 * $weight;
                    $fats = $weight;
                    break;
                case '1.725':
                    $proteins = 2 * $weight;
                    $fats = 1.1 * $weight;
                    break;
                case '1.95':
                    $proteins = 2 * $weight;
                    $fats = 1.3 * $weight;
                    break;
                default:
                    break;
            }
        } else if($user->goal == 'increase') {
            $calories += 500;
            $proteins = 2 * $weight;
            $fats = 1.2 * $weight;
        } else {
            $proteins = 2 * $weight;
            switch ($user->activity) {
                case '1.375':
                case '1.55':
                case '1.2':
                    $fats = $weight;
                    break;
                case '1.725':
                    $fats = 1.1 * $weight;
                    break;
                case '1.95':
                    $fats = 1.3 * $weight;
                    break;
                default:
                    break;
            }
        }

        return  [
            'calories' => $calories,
            'proteins' => $proteins,
            'fats' => $fats,
            'weight' => $weight,
        ];

    }
}
