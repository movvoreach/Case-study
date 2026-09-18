<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseApiController extends Controller
{
    public function __construct(private readonly CourseService $courseService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return CourseResource::collection($this->courseService->getAll($request->only(['category_id', 'department_id', 'teacher_id', 'search'])));
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->courseService->create($request->validated());

        return (new CourseResource($course->load('category')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Course $course): CourseResource
    {
        return new CourseResource($course->load('category'));
    }

    public function update(UpdateCourseRequest $request, Course $course): CourseResource
    {
        $this->courseService->update($course, $request->validated());

        return new CourseResource($course->fresh()->load('category'));
    }

    public function destroy(Course $course): JsonResponse
    {
        try {
            $this->courseService->delete($course);
        } catch (QueryException) {
            return response()->json([
                'message' => 'Course cannot be deleted because it is used by other records.',
            ], 409);
        }

        return response()->json([
            'message' => 'Course deleted successfully.',
        ]);
    }
}
