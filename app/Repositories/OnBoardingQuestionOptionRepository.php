<?php
namespace App\Repositories;

use App\Models\OnBoardingQuestion;
use App\Models\OnBoardingQuestionOption;


class OnBoardingQuestionOptionRepository{


    public function createOption(array $data){
        return OnBoardingQuestionOption::create($data);
    }

    public function deleteOption($questionId,$optionValue){
        $question = OnBoardingQuestion::find($questionId);
        if(!$question){
            return null;
        }

        $option = $question->options()->where('name_option',$optionValue)->first();

        if(!$option){
            return null;
        }
        $option->delete();
        return $option;
    }
}

