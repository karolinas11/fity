<?php
namespace App\Repositories;

use App\Models\OnBoardingQuestion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class OnBoardingQuestionRepository{
    public function getAllQuestionsWithOptions() {
        try {
            return OnBoardingQuestion::with('options')->get();
        }catch (QueryException $e) {
            Log::error('Can\'t add option: ' . $e->getMessage());
        }
    }
    public function addQuestion(array $data) {
        try {
            return OnBoardingQuestion::create($data);
        }catch(QueryException $e) {
            Log::error('Can\'t add question: ' . $e->getMessage());
        }
    }
    public function deleteQuestion($id) {
        try {
            $question=OnBoardingQuestion::find($id);
            if (!$question)
            {
                return false;
            }
            return $question->delete();
        }catch(QueryException $e) {
            Log::error('Can\'t delete question: ' . $e->getMessage());
        }
    }
    public function updateQuestion($id, $data){
        try{
            $question = OnBoardingQuestion::find($id);
            if (!$question){
                return false;
            }
            $question->update($data);
            return $question;
        }catch(QueryException $e) {
            Log::error('Can\'t update question: ' . $e->getMessage());
        }
    }
}
