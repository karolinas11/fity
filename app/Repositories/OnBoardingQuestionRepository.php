<?php
namespace App\Repositories;

use App\Models\OnBoardingQuestion;
class OnBoardingQuestionRepository{


    public function getAllQuestionsWithOptions(){
        return OnBoardingQuestion::with('options')->get();
    }
    public function addQuestion(array $data){
        return OnBoardingQuestion::create($data);
    }
    public function deleteQuestion($id){
        $question=OnBoardingQuestion::find($id);
        if (!$question)
        {
            return false;
        }
        return $question->delete();
    }
}
