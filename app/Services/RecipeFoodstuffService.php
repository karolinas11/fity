<?php

namespace App\Services;

use App\Repositories\RecipeFoodstuffRepository;

class RecipeFoodstuffService
{
    protected RecipeFoodstuffRepository $recipeFoodstuffRepository;

    public function __construct() {
        $this->recipeFoodstuffRepository = new RecipeFoodstuffRepository();
    }
    public function addRecipeFoodstuff($recipeId, $foodstuffs) {
        $this->recipeFoodstuffRepository->addRecipeFoodstuff($recipeId, $foodstuffs);
    }

    public function getRecipeFoodstuffs($id) {
        return $this->recipeFoodstuffRepository->getRecipeFoodstuffs($id);
    }

    public function deleteRecipeFoodstuff($recipeId) {
        $this->recipeFoodstuffRepository->deleteRecipeFoodstuff($recipeId);
    }

}
