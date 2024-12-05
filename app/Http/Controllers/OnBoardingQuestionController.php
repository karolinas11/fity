<?php
namespace App\Http\Controllers;
use App\Models\RecipeFoodstuff;
use App\Services\OnBoardingQuestionService;
use Illuminate\Http\Request;

class OnBoardingQuestionController extends Controller{
    protected $onBoardingQuestionService;
    public function __construct(OnBoardingQuestionService $onBoardingQuestion){
        $this->onBoardingQuestionService = $onBoardingQuestion;
    }

    public function index(){
        $questions = $this->onBoardingQuestionService->getOnBoardingQuestions();

        return view ('boarding-question', compact('questions'));
    }
    public function deleteQuestion(Request $request){
        $questionId = $request->input('id');

        $result = $this->onBoardingQuestionService->deleteQuestion($questionId);

        return response()->json($result);
    }
    public function addQuestion(Request $request){
        $data = $request->only(['title','type','name_question']);
        $result= $this->onBoardingQuestionService->addQuestion($data);

        return response()->json([
            'success' => true,
            'question'=>$result['question']
        ]);
    }
}
