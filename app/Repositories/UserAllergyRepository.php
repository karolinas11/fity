<?php

namespace App\Repositories;

use App\Models\UserAllergy;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class UserAllergyRepository
{
    public function addUserAllergy($allergyData) {
        try {
            return UserAllergy::create($allergyData);
        } catch (QueryException $e) {
            Log::error('Can\'t add user allergy: ' . $e->getMessage());
        }
    }

}
