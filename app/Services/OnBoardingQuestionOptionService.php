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
    }

    public function deleteOption($optionId) {
        return $this->onBoardingQuestionOptionRepository->deleteOption($optionId);
    }
    public function updateOption($id, $data) {
        return $this->onBoardingQuestionOptionRepository->updateOption($id, $data);
    }
}
