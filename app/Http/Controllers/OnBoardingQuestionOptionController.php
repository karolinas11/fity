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
            'data_value' => '',
        ]);

        $option = $this->onBoardingQuestionOptionService->createOption($validated);
        return response()->json([
            'success' => true,
            'question_name' => $option->question->name_question,
            'name_option' => $option->name_option,
            'value' => $option->value,
            'subtitle' => $option->subtitle,
            'data_value' => $option->data_value,
        ]);
    }

    public function deleteOption(Request $request) {

        $request->validate([
            'option_id' => 'required|exists:on_boarding_question_options,id',
        ]);
        $option = $this->onBoardingQuestionOptionService->deleteOption($request->option_id);
        if(!$option){
            return response()->json(['error' => 'Opcija nije pronađena.'], 404);
        }
        return response()->json(['success'=> 'Opcija je uspesno izbrisana.']);
    }
    public function updateOption(Request $request, $id) {
        $validateData = $request->validate([
            'name_option'=> 'required|string',
            'value'=> 'required|string',
            'subtitle'=> 'required|string',
            'data_value' => '',
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
