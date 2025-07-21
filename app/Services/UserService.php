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

        if($user->goal == 'reduction') {
            $calories -= 500;
        } else if($user->goal == 'increase') {
            $calories += 300;
            $proteins = 2 * $weight;
            $fats = 1.2 * $weight;
        } else {
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

            $proteins = match ($user->activity) {
                '1.2' => 1.6 * $weight,
                '1.375' => 1.8 * $weight,
                default => 2 * $weight,
            };
        }

        return  [
            'calories' => $calories,
            'proteins' => $proteins,
            'fats' => $fats,
            'weight' => $weight,
            'carbs' => ($calories - ($proteins * 4) - ($fats * 9)) / 4
        ];

    }

    public function getMacrosForUser2($user) {
        if($user->gender == 'm') {
            $calories = 66.47 + (13.75 * $user->weight) + (5.003 * $user->height) - (6.755 * $user->age);
            $weight = 48 + (1.1 * ($user->height - 152.4));
        } else {
            $calories = 655.1 + (9.563 * $user->weight) + (1.85 * $user->height) - (4.676 * $user->age);
            $weight = 45 + (0.9 * ($user->height - 152.4));
        }
        $calories = $calories * (double)$user->activity;

        $proteins = 0;
        $fats = 0;

        $weightNew = null;
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

        if($user->goal == 'reduction') {
            if($user->gender == 'm') {
                $abs = abs($user->weight - $weight);
                if($abs > 30) {
                    $caloriesAdd = match ($user->activity) {
                        '1.2' => 700,
                        '1.375' => 1000,
                        default => 1500,
                    };
                } else if($abs > 20) {
                    $caloriesAdd = match ($user->activity) {
                        '1.2' => 500,
                        '1.375' => 800,
                        default => 1200,
                    };
                } else if($abs > 10) {
                    $caloriesAdd = match ($user->activity) {
                        '1.2' => 500,
                        '1.375' => 700,
                        default => 900,
                    };
                } else if($abs > 0) {
                    $caloriesAdd = match ($user->activity) {
                        '1.2', '1.375' => 500,
                        default => 800,
                    };
                }
            } else {
                $caloriesAdd = 655.1 + (9.563 * $user->weight) + (1.85 * $user->height) - (4.676 * $user->age);
                $weight = 45 + (0.9 * ($user->height - 152.4));

                $abs = abs($user->weight - $weight);
                if($abs > 30) {
                    $caloriesAdd = match ($user->activity) {
                        '1.2' => 500,
                        '1.375' => 600,
                        default => 800,
                    };
                } else if($abs > 20) {
                    $caloriesAdd = match ($user->activity) {
                        '1.2', '1.375' => 500,
                        default => 600,
                    };
                } else if($abs > 10) {
                    $caloriesAdd = match ($user->activity) {
                        '1.2', '1.375' => 500,
                        default => 600,
                    };
                } else if($abs > 0) {
                    $caloriesAdd = match ($user->activity) {
                        '1.2' => 200,
                        '1.375' => 500,
                        default => 500,
                    };
                }
            }

            $calories -= $caloriesAdd;
        } else if($user->goal == 'increase') {
            $calories += 300;
            $proteins = 2 * $weight;
            $fats = 1.2 * $weight;

            $weightNew = $user->weight + 1.29;
        } else {
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

            $proteins = match ($user->activity) {
                '1.2' => 1.6 * $weight,
                '1.375' => 1.8 * $weight,
                default => 2 * $weight,
            };
        }

        return  [
            'calories' => number_format($calories, 0),
            'proteins' => number_format($proteins, 0),
            'fats' => number_format($fats, 0),
            'weight' => $weightNew? $weightNew :$weight,
            'carbohydrates' => number_format(($calories - ($proteins * 4) - ($fats * 9)) / 4, 0),
            'water' => number_format($weight * 0.03, 0)
        ];

    }

    public function assignFirebaseUid($userId, $firebaseUid, $email, $name) {
        return $this->userRepository->assignFirebaseUid($userId, $firebaseUid, $email, $name);
    }
}
