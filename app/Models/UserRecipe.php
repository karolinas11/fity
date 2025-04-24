<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRecipe extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function foodstuffs() {
        return $this->hasMany(UserRecipeFoodstuff::class);
    }
}
