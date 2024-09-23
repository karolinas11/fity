<?php

namespace App\Repositories;

use App\Models\Foodstuff;
use Illuminate\Support\Facades\Log;

class FoodstuffRepository
{
    public function addFoodstuff($foodstuffData) {
        try {
            return Foodstuff::create($foodstuffData);
        } catch (\Exception $e) {
            Log::error('Can\'t add foodstuff: ' . $e->getMessage());
        }
    }
}
