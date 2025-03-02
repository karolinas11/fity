<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'images';

    public function recipe_images()
    {
        return $this->belongsTo(Recipe::class, 'recipes_id', 'id');
    }
}
