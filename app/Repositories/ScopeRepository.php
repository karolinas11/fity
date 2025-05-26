<?php

namespace App\Repositories;

use App\Models\Scope;
use Illuminate\Support\Facades\Log;

class ScopeRepository
{

    public function addScope($scopeData) {
        try {
            return Scope::create($scopeData);
        } catch (\Exception $e) {
            Log::error('Can\'t add scope: ' . $e->getMessage());
        }
    }

    public function getUserScopes($userId) {
        try {
            return Scope::where('user_id', $userId)->get();
        } catch (\Exception $e) {
            Log::error('Can\'t get user scopes: ' . $e->getMessage());
        }
    }

}
