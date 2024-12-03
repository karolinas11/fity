<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnBoardingQuestionOption extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'on_boarding_question_option';
    public function question(){
        return $this->belongsTo(OnBoardingQuestion::class, 'question_id');
    }
}
