<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OnBoardingQuestionOptionService;
class OnBoardingQuestionOptionController {
    protected OnBoardingQuestionOptionService $onBoardingQuestionOptionService;

    public function __construct() {
        $this->onBoardingQuestionOptionService = new OnBoardingQuestionOptionService();
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'question_id' => 'required|exists:on_boarding_questions,id',
            'name_option' => 'required|string',
            'value' => 'required|string',
            'subtitle' => '',
        ]);

        $option = $this->onBoardingQuestionOptionService->createOption($validated);
        return response()->json([
            'success' => true,
            'question_name' => $option->question->name_question,
            'name_option' => $option->name_option,
            'value' => $option->value,
            'subtitle' => $option->subtitle,
        ]);
    }

    public function deleteOption(Request $request) {
        $request->validate([
            'question_id' => 'required|exists:on_boarding_questions,id',
            'value' => 'required',

        ]);
        $option = $this->onBoardingQuestionOptionService->deleteOption($request->question_id, $request->value);
        if (!$option) {
            return response()->json(['error' => 'Opcija nije pronađena.'], 404);
        }
        return response()->json(['success' => 'Opcija je uspešno obrisana.']);
    }
    public function updateOption(Request $request, $id) {
        $validateData = $request->validate([
            'name_option'=> 'required|string',
            'value'=> 'required|string',
            'subtitle'=> 'required|string',
        ]);
        $updateOption = $this->onBoardingQuestionOptionService->updateOption($id, $validateData);

        if ($updateOption) {
            return response()->json([
                'success' => true,
                'message' =>'Opcija je uspesno azurirana!',
                'option' => $updateOption,
            ]);
        }
    }
}
