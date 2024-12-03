<?php

namespace App\Services;

use App\Repositories\OnBoardingQuestionRepository;

class OnBoardingQuestionService{
    protected $onBoardingQuestionRepository;
    public function __construct(OnBoardingQuestionRepository $onBoardingQuestionRepository){
         $this->onBoardingQuestionRepository = $onBoardingQuestionRepository;
    }
    public function getOnBoardingQuestions(){
        return $this->onBoardingQuestionRepository->getAllQuestionsWithOptions();
    }
}
