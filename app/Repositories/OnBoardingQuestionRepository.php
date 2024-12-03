<?php
namespace App\Repositories;

use App\Models\OnBoardingQuestion;
class OnBoardingQuestionRepository{


    public function getAllQuestionsWithOptions(){
        return OnBoardingQuestion::with('options')->get();
    }
}
