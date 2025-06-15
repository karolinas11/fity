<?php

namespace App\Repositories;

use App\Models\UserWater;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class UserWaterRepository
{
    public function updateUserWater($userId, $water)
    {
        try {
            $today = Carbon::today()->format('Y-m-d');
            $userWater = UserWater::where('user_id', $userId)
                ->where('date', $today)
                ->get()
                ->first();
            if($userWater == null) {
                return UserWater::create(['user_id' => $userId, 'water' => $water, 'date' => $today]);
            } else {
                $userWater->water = $water;
                $userWater->save();
                return $userWater;
            }
        } catch (QueryException $e) {
            Log::error('Can\'t update water: ' . $e->getMessage());
        }

    }
}
