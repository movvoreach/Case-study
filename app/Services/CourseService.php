<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

class CourseService
{
    public function getAll(array $filters = []): Collection
    {
        $query = Course::with(['category', 'department', 'teachers', 'lessons']);

        if (!empty($filters['category_id'])) {
            $query->where('course_category_id', $filters['category_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['teacher_id'])) {
            $query->whereHas('teachers', function ($q) use ($filters) {
                $q->where('teachers.teacher_id', $filters['teacher_id']);
            });
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('course_name', 'like', "%{$search}%")
                  ->orWhere('course_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->latest('course_id')->get();
    }

    public function create(array $data): Course
    {
        return Course::create([
            'course_category_id' => $data['course_category_id'],
            'department_id' => $data['department_id'] ?? null,
            'course_code' => $this->generateCode(),
            'course_name' => $data['course_name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function update(Course $course, array $data): bool
    {
        return $course->update([
            'course_category_id' => $data['course_category_id'],
            'department_id' => $data['department_id'] ?? null,
            'course_name' => $data['course_name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function delete(Course $course): bool
    {
        return $course->delete();
    }

    public function generateCode(): string
    {
        $nextId = (int) Course::max('course_id') + 1;

        do {
            $code = 'CRS-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
            $nextId++;
        } while (Course::where('course_code', $code)->exists());

        return $code;
    }
}
