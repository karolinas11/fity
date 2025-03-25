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
                    'dataValue' => $option->data_value,
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
                if($key == 'weight') continue;
                $name = '';
                $unit = '';
                switch($key) {
                    case 'calories': $name = 'Kalorije'; $unit = ' kcal'; break;
                    case 'fats': $name ='Masti'; $unit = 'g' . ',0.7'; break;
                    case 'proteins': $name ='Proteini'; $unit = 'g' . ',0.7'; break;
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

            $answers = [];

            $goal = '';
            switch ($user->goal) {
                case 'reduction':
                    $goal = 'gubitak';
                    break;
                case 'increase':
                    $goal = 'dobitak';
                    break;
                default:
                    $goal = 'nema';
            }

            $weightDiff = abs($user->weight - $macros['weight']);
            $weightDiffTo = $weightDiff + 2;
            $answers[0] = [
                'answerIndex' => 0,
                'answerTitle' => 'Očekivani ' . $goal . ' telesne mase na mesečnom nivou iznosi,' . 'od ' . $weightDiff . ' do ' . $weightDiffTo . 'kg',
                'answerDetail' => $user->weight . ' kg,' . $macros['weight'] . ' kg',
                'dataType' => 'weight',
                'dataValue' => null
            ];

            $singleQuestion = [
                'id' => 6,
                'question' => 'Kako će izgledati tvoj napredak',
                'description' => '',
                'type' => 'chart',
                'answers' => $answers
            ];

            array_push($finalQuestions, $singleQuestion);

            $responseQuestions = [
                'questionsPageCount' => 6,
                'submitForCalculationAfterId' => 4,
                'submitForResultAfterId' => 8,
                'questions' => $finalQuestions
            ];

            return $responseQuestions;
        }
    }

}
