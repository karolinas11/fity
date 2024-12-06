<?php

namespace App\Services;

use App\Repositories\OnBoardingQuestionRepository;
use Illuminate\Support\Facades\Validator;

class OnBoardingQuestionService{
    protected OnBoardingQuestionRepository $onBoardingQuestionRepository;
    public function __construct() {
         $this->onBoardingQuestionRepository = new OnBoardingQuestionRepository();

    }
    public function getOnBoardingQuestions() {
        return $this->onBoardingQuestionRepository->getAllQuestionsWithOptions();
    }
    public function deleteQuestion($id) {
        return $this->onBoardingQuestionRepository->deleteQuestion($id);
    }
    public function addQuestion($data) {
        return $this->onBoardingQuestionRepository->addQuestion($data);
    }
}
