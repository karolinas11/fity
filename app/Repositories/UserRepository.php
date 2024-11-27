<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    public function addUser($userData) {
        try {
            return User::create($userData);
        } catch (QueryException $e) {
            Log::error('Can\'t add user: ' . $e->getMessage());
        }
    }

    public function editUser($userData, $userId){
            try{
                $user= User::find($userId);
                if ( $user ){
                    $user->update($userData);
                    return $user;
                }
                return null;

            }catch(QueryException $e){
                Log::error('Can\'t edit user: ' . $e->getMessage());
            }
    }
}
