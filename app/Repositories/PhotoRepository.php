<?php

namespace App\Repositories;

use App\Models\Photo;
use Illuminate\Support\Facades\Log;

class PhotoRepository
{

    public function addPhoto($photoData) {
        try {
            return Photo::create($photoData);
        } catch (\Exception $e) {
            Log::error('Can\'t add photo: ' . $e->getMessage());
        }
    }

    public function getUserPhotos($userId) {
        try {
            return Photo::where('user_id', $userId)->get();
        } catch (\Exception $e) {
            Log::error('Can\'t get user photos: ' . $e->getMessage());
        }
    }

}
