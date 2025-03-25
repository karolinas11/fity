<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\OnBoardingQuestionRepository;
use Illuminate\Support\Facades\Validator;

class OnBoardingQuestionService{
    protected OnBoardingQuestionRepository $onBoardingQuestionRepository;
    protected UserService $userService;
    public function __construct() {
         $this->onBoardingQuestionRepository = new OnBoardingQuestionRepository();
         $this->userService = new UserService();
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
    public function updateQuestion($id, $data) {
        return $this->onBoardingQuestionRepository->updateQuestion($id, $data);
    }

    public function getOnBoardingQuestionsByIndexAndLang($index, $lang) {
        $questions = $this->getOnBoardingQuestions();
        $finalQuestions = [];
        foreach ($questions as $question) {
            $answers = [];
            foreach ($question->options as $key => $option) {
                $singleAnswer = [
                    'answerIndex' => $key,
                    'answerTitle' => $option->value,
                    'answerDetail' => $option->subtitle,
                    'dataType' => $option->name_option,
                    'dataValue' => $option->data_value
                ];
                array_push($answers, $singleAnswer);
            }

            $singleQuestion = [
                'id' => $question->id,
                'question' => $question->title,
                'description' => $question->description,
                'type' => $question->type == 'select' ? 'choice' : $question->type,
                'answers' => $answers
            ];
            array_push($finalQuestions, $singleQuestion);
        }

        $responseQuestions = [
            'questionsPageCount' => 4,
            'submitForCalculationAfterId' => 4,
            'submitForResultAfterId' => 8,
            'questions' => $finalQuestions
        ];

        if($index == 1) {
            return $responseQuestions;
        } else {
            $user = User::find(30);
            $macros = $this->userService->getMacrosForUser($user);
            $i = 0;
            $answers = [];
            foreach ($macros as $key => $macro) {
                $name = '';
                $unit = '';
                switch($key) {
                    case 'calories': $name = 'Kalorije'; $unit = ' kcal'; break;
                    case 'fats': $name ='Masti'; $unit = 'g'; break;
                    case 'proteins': $name ='Proteini'; $unit = 'g'; break;
                    default: $name = $key; $unit = 'g';
                }
                $singleAnswer = [
                    'answerIndex' => $i,
                    'answerTitle' => $name,
                    'answerDetail' => $macro . $unit,
                    'dataType' => $key,
                    'dataValue' => null
                ];
                array_push($answers, $singleAnswer);
                $i++;
            }

            $singleQuestion = [
                'id' => 5,
                'question' => 'Tvoj dnevni plan unosa kalorija i makrosa',
                'description' => '',
                'type' => 'calculation',
                'answers' => $answers
            ];

            array_push($finalQuestions, $singleQuestion);

            $responseQuestions = [
                'questionsPageCount' => 5,
                'submitForCalculationAfterId' => 4,
                'submitForResultAfterId' => 8,
                'questions' => $finalQuestions
            ];

            return $responseQuestions;
        }
    }

}
