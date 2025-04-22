<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function foodstuffs() {
        return $this->belongsToMany(Foodstuff::class, 'recipe_foodstuffs');
    }

    public function galleryImages() {
        return $this->hasMany(Image::class, 'recipe_id');
    }
}
