<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    protected UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }
    public function addUser($userData) {
        return $this->userRepository->addUser($userData);
    }
}
