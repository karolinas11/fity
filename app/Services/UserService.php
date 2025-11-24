<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DateTime;
use DateTimeZone;

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
        $strAct = (string)$user->activity;

        $proteins = 0;
        $fats = 0;

        $weightNew = null;
        switch ($strAct) {
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
                    $caloriesAdd = match ($strAct) {
                        '1.2' => 700,
                        '1.375' => 1000,
                        default => 1500,
                    };
                } else if($abs > 20) {
                    $caloriesAdd = match ($strAct) {
                        '1.2' => 500,
                        '1.375' => 800,
                        default => 1200,
                    };
                } else if($abs > 10) {
                    $caloriesAdd = match ($strAct) {
                        '1.2' => 500,
                        '1.375' => 700,
                        default => 900,
                    };
                } else if($abs > 0) {
                    $caloriesAdd = match ($strAct) {
                        '1.2', '1.375' => 500,
                        default => 800,
                    };
                }
            } else {
                $caloriesAdd = 655.1 + (9.563 * $user->weight) + (1.85 * $user->height) - (4.676 * $user->age);
                $weight = 45 + (0.9 * ($user->height - 152.4));

                $abs = abs($user->weight - $weight);
                if($abs > 30) {
                    $caloriesAdd = match ($strAct) {
                        '1.2' => 500,
                        '1.375' => 600,
                        default => 800,
                    };
                } else if($abs > 20) {
                    $caloriesAdd = match ($strAct) {
                        '1.2', '1.375' => 500,
                        default => 600,
                    };
                } else if($abs > 10) {
                    $caloriesAdd = match ($strAct) {
                        '1.2', '1.375' => 500,
                        default => 600,
                    };
                } else if($abs > 0) {
                    $caloriesAdd = match ($strAct) {
                        '1.2' => 200,
                        '1.375' => 500,
                        default => 500,
                    };
                }
            }

            $calories -= $caloriesAdd;
            $weightNew = $user->weight - 1.29;
        } else if($user->goal == 'increase') {
            $calories += 300;
            $proteins = 2 * $weight;
            $fats = 1.2 * $weight;

            $weightNew = $user->weight + 1.29;
        } else {
            switch ($strAct) {
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

            $proteins = match ($strAct) {
                '1.2' => 1.6 * $weight,
                '1.375' => 1.8 * $weight,
                default => 2 * $weight,
            };

            $weightNew = $user->weight;
        }

        return  [
            'calories' => number_format($calories, 0, '.', ''),
            'proteins' => number_format($proteins, 0, '.', ''),
            'fats' => number_format($fats, 0, '.', ''),
            'weight' => $weightNew? $weightNew :$weight,
            'carbohydrates' => number_format(($calories - ($proteins * 4) - ($fats * 9)) / 4, 0, '.', ''),
            'water' => number_format($weight * 0.03, 1, '.', ''),
        ];

    }

    public function assignFirebaseUid($userId, $firebaseUid, $email, $name) {
        return $this->userRepository->assignFirebaseUid($userId, $firebaseUid, $email, $name);
    }

    public function nextDateForDay(
        string $day,
        string $tz = 'Europe/Belgrade',
        ?string $fromDate = null,
        string $format = 'Y-m-d' // npr. 2025-09-11
    ): ?string {
        $map = [
            'PON' => 'Monday',
            'UTO' => 'Tuesday',
            'SRE' => 'Wednesday',
            'CET' => 'Thursday',
            'PET' => 'Friday',
            'SUB' => 'Saturday',
            'NED' => 'Sunday',
        ];

        $key = mb_strtoupper(trim($day), 'UTF-8');
        if (!isset($map[$key])) return null;

        $tzObj  = new DateTimeZone($tz);
        $base   = $fromDate ? new DateTime($fromDate, $tzObj) : new DateTime('now', $tzObj);
        $target = (clone $base)->modify('next ' . $map[$key]); // uvek sledeći (ne “danas”)

        return $target->format($format);
    }

    public function updateSubscriberFields(string $email, array $customFields)
    {
        // Koristimo POST na /subscribers endpoint jer on radi "Update or Create"
        $response = Http::withToken('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI0IiwianRpIjoiYTRjMDAwNTgzNjZlMDEzMDI0MzMyNzJjOTgwYTA0NzMxMzViNDMxNjZhNzFiZGMwZjQ3ODY4MDFiODgwZDI5OTU4NDI1NTVjNDk5MjgyNTkiLCJpYXQiOjE3NTUxOTc5MTAuNDAxMzMsIm5iZiI6MTc1NTE5NzkxMC40MDEzMzMsImV4cCI6NDkxMDg3MTUxMC4zOTc2OCwic3ViIjoiODA1NDMyIiwic2NvcGVzIjpbXX0.WHVKyWScwktAHSdWtYEnCwPjMdmj10BPsb-xnhVtdIJZw3CBorz4gusjXukavPucBBslNbOL2RPXntvb50fp2riCPQn_AUo6jzEXIkmlivILqDtl_0KiIdjXlsO3oLA2CqlPG6hWDAjmmtO3ELGNN732akMxzjJY8qvOKXU5GBkpoC1E3jDrptU3sLmjkcSmGo19Avsc2jRRAmJLRb6WecAFkZzCzB9Esp-QLUOQaotOFzOz9zcC5XLW_ob1ktoa0hwWQItSFFBAOxz6nQteppsBSP7URk_7awd7BbsJrvY1bUGzxDkmawcfI9j_b7YCSCiCZltecSG25ofg_MQddHPmdLSKVgMaixCEW8PeMtg-zS4NH0l_OyW5TkzNzvQcoY7o9-lzNUWCbkqcupHo9HULUxZPpa2I9sl1-Ln7-YL98GBKUu_Uvhb0SPCOa3kfa-Zve2tJwdHyYi8uiA1FlLyU1wP8DF3JgCoz6fTF1pSWUGkk3YmVsKj18H-re2IqJfTveLurUfPuGbIknracxoQtxomgTovthofXEMZlHLmjuU6LeA2gynVOBrb-ssJM9BQ3bBoMWHelnbAk3Yg4aOu4m4UKfu-CaBxAi7CCQohmdWbtO84U2d1IwdjR8xfOZHt4MMaJJT19NAiL7rVn27tMdHgypME53Z9BAu0Pr3Y')
            ->acceptJson()
            ->post('https://connect.mailerlite.com/api/subscribers', [
                'email' => $email,
                'fields' => $customFields,
            ]);

        // 3. Provera odgovora
        if ($response->successful()) {
            return true;
        } else {
            // Logujemo grešku da znamo šta nije u redu (npr. loš API ključ ili loš format)
            Log::error('MailerLite Update Error za ' . $email . ': ' . $response->body());
            return false;
        }
    }

}
