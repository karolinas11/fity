<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodstuffCategory extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function foodstuffsOption() {
        return $this->hasMany(Foodstuff::class, 'foodstuff_category_id');
    }
}
