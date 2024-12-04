<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OnBoardingQuestionOptionService;
class OnBoardingQuestionOptionController {
    protected OnBoardingQuestionOptionService $onBoardingQuestionOptionService;

    public function __construct(OnBoardingQuestionOptionService $onBoardingQuestionOptionService) {
        $this->onBoardingQuestionOptionService = $onBoardingQuestionOptionService;
    }

    public function store(Request $request){
            $validated = $request->validate([
                'question_id' => 'required|exists:on_boarding_question,id',
                'name_option' => 'required|string',
                'value' => 'required|string',
            ]);

            $response=$this->onBoardingQuestionOptionService->createOption($validated);

            return response()->json($response);
    }
}
