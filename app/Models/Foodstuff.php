<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foodstuff extends Model
{
    use HasFactory;

    protected $guarded = [];

   /* public function foodstuffAllCategory(){
        return $this->belongsTo(FoodstuffCategory::class, 'category_id');
    }*/
}


