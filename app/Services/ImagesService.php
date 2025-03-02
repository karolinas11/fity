<?php
namespace App\Services;

use App\Repositories\ImagesRepository;

class ImagesService {
    protected ImagesRepository $imagesRepository;
    public function __construct() {
        $this->imagesRepository = new ImagesRepository();
    }

    public function addImages($recipeId, $imagePath) {
        return $this->imagesRepository->addImages($recipeId, $imagePath);
    }
}
