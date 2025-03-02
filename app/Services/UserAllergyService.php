<?php

namespace App\Services;

use App\Repositories\UserAllergyRepository;

class UserAllergyService
{
    protected $userAllergyRepository;
    public function __construct() {
        $this->userAllergyRepository = new UserAllergyRepository();
    }

    public function addUserAllergy($allergyData) {
        return $this->userAllergyRepository->addUserAllergy($allergyData);
    }
}
