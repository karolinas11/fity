<?php

namespace App\Services;

use App\Repositories\PhotoRepository;

class PhotoService
{

    protected PhotoRepository $photoRepository;

    public function __construct() {
        $this->photoRepository = new PhotoRepository();
    }

    public function addPhoto($photoData) {
        return $this->photoRepository->addPhoto($photoData);
    }

    public function getUserPhotos($userId) {
        return $this->photoRepository->getUserPhotos($userId);
    }

}
