<?php

namespace App\Services;

use App\Repositories\RecipeFoodstuffRepository;

class RecipeFoodstuffService
{
    protected RecipeFoodstuffRepository $recipeFoodstuffRepository;

    public function __construct() {
        $this->recipeFoodstuffRepository = new RecipeFoodstuffRepository();
    }
    public function addRecipeFoodstuff($recipeFoodstuffData) {
        return $this->recipeFoodstuffRepository->addRecipeFoodstuff($recipeFoodstuffData);
    }
}
