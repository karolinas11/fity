<?php
namespace App\Http\Controllers;
use App\Models\Foodstuff;
use App\Models\RecipeFoodstuff;
use App\Models\User;
use App\Models\UserAllergy;
use App\Services\OnBoardingQuestionService;
use App\Services\UserService;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OnBoardingQuestionController extends Controller {
    protected OnBoardingQuestionService $onBoardingQuestionService;
    protected UserService $userService;
    public function __construct() {
        $this->onBoardingQuestionService =new OnBoardingQuestionService();
        $this->userService = new UserService();
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

        $requestData = $request->all();
        $goal = '';

//        Log::error(json_encode($requestData));
//        exit;

        $question0 = json_decode($requestData['question_0'], true);
        $question1 = json_decode($requestData['question_1'], true);
        $question2 = json_decode($requestData['question_2'], true);
        $question3 = json_decode($requestData['question_3'], true);

        switch ($question0[0]['value']) {
            case 'Redukcija telesne mase':
                $goal = 'reduction';
                break;
            case 'Održavanje telesne mase':
                $goal = 'stable';
                break;
            case 'Uvećanje telesne mase':
                $goal = 'increase';
                break;
        }

        $gender = '';
        $gen = $hei = $wei = $age = null;
        foreach ($question1 as $key => $value) {
            if($value['index'] == 0) {
                $hei = $value['value'];
            } else if($value['index'] == 1) {
                $wei = $value['value'];
            } else if($value['index'] == 2) {
                $age = $value['value'];
            } else if($value['index'] == 3) {
                $gen = $value['value'];
            }
        }

        switch ($gen) {
            case 'Muško':
                $gender = 'm';
                break;
            case 'Žensko':
                $gender = 'f';
                break;
        }

        $activity = '';
        switch ($question2[0]['value']) {
            case 'Nimalo aktivni':
                $activity = 1.2;
                break;
            case 'Slabo aktivni':
                $activity = 1.375;
                break;
            case 'Srednje aktivni':
                $activity = 1.55;
                break;
            case 'Vrlo aktivni':
                $activity = 1.725;
                break;
            case 'Ekstremno aktivni':
                $activity = 1.95;
                break;
        }

        $insulinResistance = $question3[0]['value'] == 'Da' ? 1 : 0;

        $userData = [
            'goal' => $goal,
            'height' => $hei,
            'weight' => $wei,
            'age' => $age,
            'gender' => $gender,
            'activity' => $activity,
            'tolerance_proteins'=> 5,
            'tolerance_fats'=> 5,
            'tolerance_calories'=> 50,
            'meals_num'=> 4,
            'days'=> 30,
            'insulin_resistance' => $insulinResistance
        ];
        $user = $this->userService->addUser($userData);
        $data = $this->onBoardingQuestionService->getOnBoardingQuestionsByIndexAndLang(2, 'en', $user);

        return response()->json($data, '200');
    }

    public function saveSecondAnswers(Request $request) {
        Log::error('saveSecondAnswers: ', [$request->all()]);
        $requestData = $request->all();
        $userId = $requestData['userId'];
        $user = User::find($userId);

        $question6 = json_decode($requestData['answers']['question_6'], true);
        $question7 = json_decode($requestData['answers']['question_7'], true);

        $mealsNum = 0;
        foreach ($question7 as $key => $value) {
            if($value['value'] == 'true') {
                $mealsNum++;
            }
        }

        $foodstuffs = $question6['ingredients'];
        $foodstuffsArray = explode(',', $foodstuffs);
        foreach ($foodstuffsArray as $foodstuff) {
            UserAllergy::create([
                'user_id' => $userId,
                'foodstuff_id' => Foodstuff::where('name', $foodstuff)->first()->id
            ]);
        }

        $user->meals_num = $mealsNum;
        $user->days = 7;
        $user->save();

        $target = $this->userService->getMacrosForUser($user);
        $response = Http::timeout(10000)
            ->withoutVerifying()
            ->post('https://fity-algorithm.fly.dev/meal-plan', [
                'target_calories' => $target['calories'],
                'target_protein' => $target['proteins'],
                'target_fat' => $target['fats'],
                'meals_num' => $user->meals_num,
                'tolerance_calories' => $user->tolerance_calories,
                'tolerance_proteins' => $user->tolerance_proteins,
                'tolerance_fats' => $user->tolerance_fats,
                'days' => $user->days
            ]);


        $data = $response->json();

        foreach ($data['daily_plans'] as $day) {
            if(!$day['exists']) continue;
            $date = date('Y-m-d', strtotime('+' . $day['day'] . ' days'));
        }

        return response()->json($userId, '200');
    }

}
