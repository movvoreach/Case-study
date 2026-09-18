<?php

namespace App\Http\Controllers;

use App\Models\ContentLesson;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::with(['category', 'department', 'lessons'])->get();
        $students = Student::with(['enrollments.course'])->latest('student_id')->take(20)->get();
        $enrollments = Enrollment::with(['student', 'course'])->latest('enrollment_id')->get();
        $totalLessons = ContentLesson::count();

        return view('progress.index', compact('courses', 'students', 'enrollments', 'totalLessons'));
    }
}
