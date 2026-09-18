<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Services\CourseService;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use App\Models\Department;

class CourseController extends Controller
{
    public function __construct(private readonly CourseService $courseService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['category_id', 'department_id', 'teacher_id', 'search']);
        $courses = $this->courseService->getAll($filters);

        $categories = CourseCategory::orderBy('category_name')->get();
        $departments = Department::orderBy('department_name')->get();
        $teachers = Teacher::orderBy('first_name')->get();
        $students = Student::all();

        $enrolledStudentsMap = Enrollment::select('course_id', 'student_id')
            ->get()
            ->groupBy('course_id')
            ->map(function ($items) {
                return $items->pluck('student_id');
            });

        $assignedTeachersMap = DB::table('course_teacher')
            ->select('course_id', 'teacher_id')
            ->get()
            ->groupBy('course_id')
            ->map(function ($items) {
                return $items->pluck('teacher_id');
            });

        return view('courses.index', compact(
            'courses',
            'categories',
            'departments',
            'teachers',
            'students',
            'enrolledStudentsMap',
            'assignedTeachersMap',
            'filters'
        ));
    }

    public function assignTeacher(Request $request): RedirectResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,course_id',
            'teacher_id' => 'required|exists:teachers,teacher_id',
        ]);

        $course = Course::findOrFail($request->course_id);
        
        if (!$course->teachers()->where('teachers.teacher_id', $request->teacher_id)->exists()) {
            $course->teachers()->attach($request->teacher_id);
        }

        return back()->with('success', 'បានចាត់តាំងគ្រូដោយជោគជ័យ។');
    }

    public function create(): View
    {
        $courseCode = $this->courseService->generateCode();
        $courseCategories = CourseCategory::orderBy('category_name')->get();
        $departments = Department::orderBy('department_name')->get();

        return view('courses.create', compact('courseCode', 'courseCategories', 'departments'));
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $this->courseService->create($request->validated());

        return redirect()
            ->route('courses.index')
            ->with('success', 'បានបង្កើតវគ្គសិក្សាដោយជោគជ័យ។');
    }

    public function edit(Course $course): View
    {
        $courseCategories = CourseCategory::orderBy('category_name')->get();
        $departments = Department::orderBy('department_name')->get();

        return view('courses.edit', compact('course', 'courseCategories', 'departments'));
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->courseService->update($course, $request->validated());

        return redirect()
            ->route('courses.index')
            ->with('success', 'បានកែប្រែវគ្គសិក្សាដោយជោគជ័យ។');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->courseService->delete($course);

        return redirect()
            ->route('courses.index')
            ->with('success', 'បានលុបវគ្គសិក្សាដោយជោគជ័យ។');
    }
}
