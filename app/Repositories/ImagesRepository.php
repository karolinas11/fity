<?php
namespace App\Repositories;

use App\Models\Image;
use Illuminate\Support\Facades\Log;

class ImagesRepository {
    public function addImages($recipeId, $imagePath){
        try{
            Log::info('Dodajem sliku za recept ID ' . $recipeId . ' sa putanjom: ' . $imagePath);
            return  Image::create([
                'recipes_id' => $recipeId,
                'image_path' => $imagePath
            ]);
        }catch(\Exception $e){
            Log::error('Can\'t add images : '.$e->getMessage());
            return response()->json(['error' => 'Došlo je do greške prilikom dodavanja slike'], 500);
        }
    }
}
