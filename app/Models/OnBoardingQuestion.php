<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnBoardingQuestion extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'on_boarding_questions';

    public function options() {
        return $this->hasMany(OnBoardingQuestionOption::class,'question_id');
    }
}
