<?php

namespace App\Http\Controllers;

use App\Models\ContentLesson;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(Request $request): View
    {
        $exams = ContentLesson::with(['course', 'courseModule'])
            ->where('content_type', 'quiz')
            ->latest('content_lesson_id')
            ->get();

        $courses = Course::orderBy('course_name')->get();

        return view('exams.index', compact('exams', 'courses'));
    }
}
