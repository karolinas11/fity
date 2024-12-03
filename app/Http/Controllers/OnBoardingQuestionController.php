<?php
namespace App\Http\Controllers;
use App\Models\RecipeFoodstuff;
use App\Services\OnBoardingQuestionService;

class OnBoardingQuestionController extends Controller{
    protected $onBoardingQuestionService;
    public function __construct(OnBoardingQuestionService $onBoardingQuestion){
        $this->onBoardingQuestionService = $onBoardingQuestion;
    }

    public function index(){
        $questions = $this->onBoardingQuestionService->getOnBoardingQuestions();

        return view ('boarding-question', compact('questions'));
    }
}
