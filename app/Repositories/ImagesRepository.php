<?php
namespace App\Repositories;

use App\Models\Image;
use Illuminate\Support\Facades\Log;

class ImagesRepository {
    public function addImages($recipeId, $imagePath){
        try{
            Log::info('Dodajem sliku za recept ID ' . $recipeId . ' sa putanjom: ' . $imagePath);
            return  Image::create([
                'recipe_id' => $recipeId,
                'image_path' => $imagePath
            ]);
        }catch(\Exception $e){
            Log::error('Can\'t add images : '.$e->getMessage());
            return response()->json(['error' => 'Došlo je do greške prilikom dodavanja slike'], 500);
        }
    }

    public function deleteRecipeImages($recipeId) {
        try {
            Image::where('recipe_id', $recipeId)->delete();
        } catch (QueryException $e) {
            Log::error('Can\'t delete images: ' . $e->getMessage());
        }
    }
}
