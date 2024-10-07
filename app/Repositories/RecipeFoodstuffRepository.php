<?php

namespace App\Repositories;

class RecipeFoodstuffRepository
{
    public function addRecipeFoodstuff($recipeFoodstuffData) {
        try {
            return RecipeFoodstuff::create($recipeFoodstuffData);
        } catch (QueryException $e) {
            Log::error('Can\'t add recipeFoodstuff: ' . $e->getMessage());
        }
    }
}
