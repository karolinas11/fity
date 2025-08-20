<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\FoodstuffCategoryRepository;
use App\Repositories\OnBoardingQuestionRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OnBoardingQuestionService{
    protected OnBoardingQuestionRepository $onBoardingQuestionRepository;
    protected FoodstuffCategoryRepository $foodstuffCategoryRepository;
    protected UserService $userService;
    public function __construct() {
         $this->onBoardingQuestionRepository = new OnBoardingQuestionRepository();
         $this->foodstuffCategoryRepository = new FoodstuffCategoryRepository();
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

    public function getOnBoardingQuestionsByIndexAndLang($index, $lang, $user = null) {
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
            'questionsPageCount' => 8,
            'submitForCalculationAfterId' => 4,
            'submitForResultAfterId' => 8,
            'questions' => $finalQuestions
        ];

        if($index == 1) {
            return $responseQuestions;
        } else {
            if($user == null) {
                $user = Auth::user();
            }
            $macros = $this->userService->getMacrosForUser2($user);
            Log::error('USER AND MACROS: ' . json_encode($user) . ' ' . json_encode($macros));
            $i = 0;
            $answers = [];

            $total = $macros['proteins'] + $macros['fats'] + $macros['carbohydrates'];
            $percentProteins = number_format(($macros['proteins'] / $total) * 100, 1, '.', '');
            $percentFats = number_format(($macros['fats'] / $total) * 100, 1, '.', '');
            $percentCarbs = number_format(($macros['carbohydrates'] / $total) * 100, 1, '.', '');

            Log::error('PERCENTS: ' . $percentProteins . ' ' . $percentFats . ' ' . $percentCarbs);

            foreach ($macros as $key => $macro) {
                if($key == 'weight') continue;
                $name = '';
                $unit = '';
                switch($key) {
                    case 'calories': $name = 'Kalorije'; $unit = ' kcal'; break;
                    case 'fats': $name ='Masti'; $unit = 'g' . ',' . $percentFats; break;
                    case 'proteins': $name ='Proteini'; $unit = 'g' . ',' . $percentProteins; break;
                    case 'carbohydrates': $name ='Ugljeni hidrati'; $unit = 'g' . ',' . $percentCarbs; break;
                    case 'water': $name = 'Voda'; $unit = 'l' . ',1.0'; break;
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
                'answers' => $answers,
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
                'answerTitle' => 'Očekivani ' . $goal . ' telesne mase na mesečnom nivou iznosi,' . 'od ' . $weightDiff . 'kg do ' . $weightDiffTo . 'kg',
                'answerDetail' => $user->weight . ',' . $macros['weight'],
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

            $foodstuffCategories = $this->foodstuffCategoryRepository->getFoodstuffCategoriesAll();
            $answers = [];
            $i = 0;
            foreach ($foodstuffCategories as $foodstuffCategory) {
                $foodstuffs = '';
                foreach ($foodstuffCategory->foodstuffsOption as $foodstuff) {
                    $foodstuffs .= $foodstuff->name . ',';
                }
                $singleAnswer = [
                    'answerIndex' => $i,
                    'answerTitle' => $foodstuffCategory->name,
                    'answerDetail' => $foodstuffs,
                    'dataType' => 'ingredients',
                    'dataValue' => null
                ];
                array_push($answers, $singleAnswer);
                $i++;
            }

            $singleQuestion = [
                'id' => 7,
                'question' => 'Isključi namirnice koje ne želiš u ishrani ili na koje si alergičan/a',
                'description' => '',
                'type' => 'ingredients',
                'answers' => $answers
            ];
            array_push($finalQuestions, $singleQuestion);

            $answers = [];
            for($i = 0; $i < 5; $i++) {
                $title = '';
                switch ($i) {
                    case 0:
                        $title = 'Doručak';
                        break;
                    case 1:
                        $title = 'Ručak';
                        break;
                    case 2:
                        $title = 'Večera';
                        break;
                    case 3:
                        $title = 'Užina 1';
                        break;
                    case 4:
                        $title = 'Užina 2';
                        break;
                }
                $singleAnswer = [
                    'answerIndex' => $i,
                    'answerTitle' => $title,
                    'answerDetail' => '',
                    'dataType' => 'meal',
                    'dataValue' => null
                ];
                array_push($answers, $singleAnswer);
            }
            $singleQuestion = [
                'id' => 8,
                'question' => 'Odaberi broj i tip obroka tokom dana',
                'description' => 'Kako biste osigurali najbolje rezultate i održivost zdravog načina ishrane preporuka je da tokom dana minimum imate tri glavna obroka i jednu užinu.',
                'type' => 'toggle',
                'answers' => $answers
            ];
            array_push($finalQuestions, $singleQuestion);

            $responseQuestions = [
                'questionsPageCount' => 8,
                'submitForCalculationAfterId' => 4,
                'submitForResultAfterId' => 8,
                'questions' => $finalQuestions,
                'userId' => (string)$user->id
            ];

            return $responseQuestions;
        }
    }

}
