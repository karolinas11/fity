<?php

namespace App\Services;
use App\Repositories\OnBoardingQuestionOptionRepository;

class OnBoardingQuestionOptionService {
    protected OnBoardingQuestionOptionRepository $onBoardingQuestionOptionRepository;

    public function __construct() {
        $this->onBoardingQuestionOptionRepository = new OnBoardingQuestionOptionRepository();
    }

    public function createOption(array $data) {
        return $this->onBoardingQuestionOptionRepository->createOption($data);
      /*  $questionName = $option->question->name_question;
        return [
            'success' => true,
            'question_name'=> $questionName,
            'name_option' => $option->name_option,
            'value' => $option->value,
        ];*/
    }

    public function deleteOption($questionId, $optionValue) {
        return $this->onBoardingQuestionOptionRepository->deleteOption($questionId, $optionValue);
    }
}
