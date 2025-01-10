<?php
namespace App\Http\Controllers;
use App\Models\RecipeFoodstuff;
use App\Services\OnBoardingQuestionService;
use Illuminate\Http\Request;

class OnBoardingQuestionController extends Controller {
    protected OnBoardingQuestionService $onBoardingQuestionService;
    public function __construct() {
        $this->onBoardingQuestionService =new OnBoardingQuestionService();
    }
    public function index() {
        $questions = $this->onBoardingQuestionService->getOnBoardingQuestions();

        return view ('boarding-question', compact('questions'));
    }
    public function deleteQuestion(Request $request) {
        $questionId = $request->input('id');

        if (!$questionId) {
            return response()->json([
                'success' => false,
                'message' => 'ID pitanja nije prosleđen.'
            ], 400);
        }

        $result = $this->onBoardingQuestionService->deleteQuestion($questionId);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Pitanje uspešno izbrisano!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Pitanje nije pronađeno ili nije moglo biti izbrisano.'
        ], 404);
    }
    public function addQuestion(Request $request) {
        $validatedData = $request->validate([
            'title' => 'required|string',
            /*'subtitle' => 'required|string',*/
            'type' => 'required|string',
            'name_question' => 'required|string|unique:on_boarding_question,name_question',
        ]);

        $question = $this->onBoardingQuestionService->addQuestion($validatedData);

        return response()->json([
            'success' => true,
            'question' => $question,
        ]);
    }
    public function updateQuestion(Request $request, $id) {
      $validateData= $request->validate([
          'title' => 'required|string|max:255',
          'type' => 'required|string|max:255',
          'name_question' => 'required|string|max:255',
      ]);

      $updateQuestion = $this->onBoardingQuestionService->updateQuestion($id, $validateData);

      if($updateQuestion) {
          return response()->json([
             'success' => true,
             'message' => 'Pitanje je uspesno azurirano!',
             'question' => $updateQuestion,
          ]);
      }
      return response()->json([
          'success' => false,
          'message' => 'Pitanja nije uspesno azurirano!'
      ]);
    }

}
