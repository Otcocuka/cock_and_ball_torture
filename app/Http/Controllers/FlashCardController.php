<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\InterviewQuestion;


class FlashCardController extends Controller
{
    public function index()
    {
        $questions = InterviewQuestion::all(); // Все 20 вопросов
        return view('flashcards', compact('questions'));
    }

}
