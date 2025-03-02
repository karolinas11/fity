<?php

namespace App\Services;

use App\Repositories\UserWaterRepository;

class UserWaterService
{
    protected UserWaterRepository $userWaterRepository;
    public function __construct() {
        $this->userWaterRepository = new UserWaterRepository();
    }

    public function updateUserWater($userId, $water) {
        return $this->userWaterRepository->updateUserWater($userId, $water);
    }
}
