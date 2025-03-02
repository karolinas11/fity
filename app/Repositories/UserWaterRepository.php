<?php

namespace App\Repositories;

use App\Models\UserWater;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class UserWaterRepository
{
    public function updateUserWater($userId, $water)
    {
        try {
            $userWater = UserWater::where('user_id', $userId)
                ->get()
                ->first();
            $userWater->water += $water;
            $userWater->save();
            return $userWater;
        } catch (QueryException $e) {
            Log::error('Can\'t update water: ' . $e->getMessage());
        }

    }
}
