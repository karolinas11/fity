<?php

namespace App\Services;

use App\Repositories\OnBoardingQuestionRepository;
use Illuminate\Support\Facades\Validator;

class OnBoardingQuestionService{
    protected $onBoardingQuestionRepository;
    public function __construct(OnBoardingQuestionRepository $onBoardingQuestionRepository){
         $this->onBoardingQuestionRepository = $onBoardingQuestionRepository;
    }
    public function getOnBoardingQuestions(){
        return $this->onBoardingQuestionRepository->getAllQuestionsWithOptions();
    }
    public function deleteQuestion($id): array {
        $isDeleted = $this->onBoardingQuestionRepository->deleteQuestion($id);
        if($isDeleted){
            return[
              'success' => true,
              'message' => 'Pitanje izbrisano uspesno!',
            ];
        }
        return[
            'success' => false,
            'message' => 'Pitanje nije pronadjeno',
        ];
    }
    public function addQuestion($data){

        $validator = Validator::make($data, [
            'title' => 'required|string',
            'type' => 'required|string',
            'name_question' => 'required|string|unique:on_boarding_question,name_question',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $question = $this->onBoardingQuestionRepository->addQuestion($data);

        return [
            'success' => true,
            'question' => $question,
        ];
    }
}
