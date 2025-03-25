<?php
namespace App\Http\Controllers;
use App\Models\RecipeFoodstuff;
use App\Services\OnBoardingQuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'description' => '',
            'type' => 'required|string',
            'name_question' => 'required|string|unique:on_boarding_questions,name_question',
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
          'description' => 'string',
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

    public function getOnboardingQuestions($questionSetIndex, $language) {
        $data = $this->onBoardingQuestionService->getOnBoardingQuestionsByIndexAndLang($questionSetIndex, $language);
        return response()->json($data, '200');
    }

    function saveFirstAnswers(Request $request) {

        Log::error('saveFirstAnswers: ', [$request->all()]);
//        $requestData =  $request->all();
//
//        $goal = '';
//        switch ($requestData['question 0'][0]['value']) {
//            case 'Redukcija telesne mase':
//                $goal = 'reduction';
//                break;
//            case 'Održavanje telesne mase':
//                $goal = 'stable';
//                break;
//            case 'Uvećanje telesne mase':
//                $goal = 'increase';
//                break;
//        }

        $json = '{
   "question 0":[
      {
         "index":0,
         "dataType":"choice",
         "value":"Redukcija telesne mase",
         "detail":null
      }
   ],
   "question 1":[
      {
         "index":0,
         "dataType":"height",
         "value":"100",
         "detail":null
      },
      {
         "index":1,
         "dataType":"weight",
         "value":"66.0",
         "detail":null
      },
      {
         "index":2,
         "dataType":"age",
         "value":"40",
         "detail":null
      },
      {
         "index":3,
         "dataType":"gender",
         "value":"Žensko",
         "detail":null
      }
   ],
   "question 2":[
      {
         "index":0,
         "dataType":"choice",
         "value":"Nimalo aktivni",
         "detail":null
      }
   ],
   "question 3":[
      {
         "index":1,
         "dataType":"choice",
         "value":"Ne",
         "detail":null
      }
   ]
}';

//        $userData = [
//            'goal' => $goal,
//            'height' => $request->input('height'),
//            'weight' => $request->input('weight'),
//            'age' => $request->input('age'),
//            'gender' => $request->input('gender'),
//            'activity' => $request->input('activity'),
//            'tolerance_proteins'=>$request->input('tolerance_proteins'),
//            'tolerance_fats'=>$request->input('tolerance_fats'),
//            'tolerance_calories'=>$request->input('tolerance_calories'),
//            'meals_num'=>$request->input('meals_num'),
//            'days'=>$request->input('days')
//        ];
        $data = $this->onBoardingQuestionService->getOnBoardingQuestionsByIndexAndLang(2, 'en');
        return response()->json($data, '200');
    }

}
