<?php
namespace App\Repositories;

use App\Models\OnBoardingQuestionOption;
class OnBoardingQuestionOptionRepository{


    public function createOption(array $data){
        return OnBoardingQuestionOption::create($data);
    }
}

