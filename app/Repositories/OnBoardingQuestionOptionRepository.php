<?php
namespace App\Repositories;

use App\Models\OnBoardingQuestion;
use App\Models\OnBoardingQuestionOption;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;


class OnBoardingQuestionOptionRepository{

    public function createOption(array $data) {
       try{
           return OnBoardingQuestionOption::create($data);
       }catch(QueryException $e){
           Log::error('Can\'t add option: ' . $e->getMessage());
       }
    }
    public function deleteOption($optionId) {
        try{
            $option = OnBoardingQuestionOption::find($optionId);
            if(!$option){
                return null;
            }
            $option->delete();
            return $option;
        }catch(QueryException $e){
            Log::error('Can\'t delete option: ' . $e->getMessage());
        }
    }
    public function updateOption($id, $data) {
        try {
            $option = OnBoardingQuestionOption::find($id);
            if (!$option) {
                return false;
            }
            $option->update($data);
            return $option;
        } catch (QueryException $e) {
            Log::error('Can\'t update option: ' . $e->getMessage());
            return false;
        }
    }
}

