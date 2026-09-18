<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContentLessonRequest;
use App\Models\ContentLesson;
use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentLessonController extends Controller
{
    public function create(Request $request)
    {
        $courses = Course::with('courseModules')->orderBy('course_name')->get();
        $modules = CourseModule::with('course')->orderBy('course_id')->orderBy('module_number')->get();
        $existingSlugs = ContentLesson::pluck('slug')->values();

        $selectedCourse = $courses->firstWhere('course_id', (int) $request->query('course_id'));
        $selectedModule = $modules->firstWhere('course_module_id', (int) $request->query('course_module_id'));

        return view('content_lessons.create', compact('courses', 'modules', 'existingSlugs', 'selectedCourse', 'selectedModule'));
    }

    public function storeContent(StoreContentLessonRequest $request)
    {
        $validated = $request->validated();
        $module = CourseModule::findOrFail($validated['course_module_id']);

        $thumbnailPath = $request->file('thumbnail')?->store('content/thumbnails', 'public');
        $videoUploadPath = $request->file('video_upload')?->store('content/videos', 'public');
        $videoThumbnailPath = $request->file('video_thumbnail')?->store('content/video-thumbnails', 'public');
        $documentPath = $request->file('document.document_file')?->store('content/documents', 'public');

        $attachmentFiles = [];
        foreach ($request->file('attachments', []) as $file) {
            if (! $file) {
                continue;
            }

            $attachmentFiles[] = [
                'name' => $file->getClientOriginalName(),
                'type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'path' => $file->store('content/attachments', 'public'),
                'downloadable' => true,
            ];
        }

        $status = $validated['status'] ?? 'draft';
        $publishedAt = null;
        if ($status === 'published' && ! empty($validated['publish_date'])) {
            $publishedAt = trim($validated['publish_date'] . ' ' . ($validated['publish_time'] ?? '00:00'));
        }

        ContentLesson::create([
            'course_id' => $module->course_id,
            'course_module_id' => $module->course_module_id,
            'module_number' => $module->module_number,
            'module_title' => $module->title,
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?: $this->uniqueSlug($validated['title']),
            'content_type' => $validated['content_type'],
            'summary' => $validated['summary'] ?? null,
            'body' => $validated['body'],
            'video_url' => $validated['video_url'] ?? null,
            'file_path' => $documentPath,
            'duration_minutes' => $validated['video_duration'] ?? null,
            'position' => $validated['position'],
            'completion_required' => $request->boolean('is_required'),
            'visibility' => $status === 'draft' ? 'hidden' : 'visible',
            'max_score' => $request->input('assignment.maximum_score'),
            'passing_score' => $request->input('quiz.passing_score'),
            'metadata' => $this->metadata($request, $validated, $thumbnailPath, $videoUploadPath, $videoThumbnailPath, $documentPath, $attachmentFiles),
            'available_from' => $publishedAt,
            'is_published' => $status === 'published',
        ]);

        return redirect()
            ->route('lessons.create')
            ->with('success', 'Content has been saved successfully.');
    }

    public function store(Request $request, CourseModule $module)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content_type' => 'required|in:lesson,page,video,file,url,assignment,quiz,forum',
            'summary' => 'nullable|string',
            'body' => 'nullable|string',
        ]);

        $validated['course_id'] = $module->course_id;
        $validated['course_module_id'] = $module->course_module_id;
        $validated['module_number'] = $module->module_number;
        $validated['module_title'] = $module->title;
        $validated['slug'] = $this->uniqueSlug($validated['title']);
        $validated['position'] = (ContentLesson::where('course_module_id', $module->course_module_id)->max('position') ?? 0) + 1;

        $module->lessons()->create($validated);

        return redirect()->back()->with('success', 'Content has been created successfully.');
    }

    public function show(ContentLesson $lesson)
    {
        if (request()->wantsJson()) {
            return response()->json($lesson->load('courseModule', 'course'));
        }

        return view('content_lessons.show', compact('lesson'));
    }

    public function update(Request $request, ContentLesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content_type' => 'required|in:lesson,page,video,file,url,assignment,quiz,forum',
            'summary' => 'nullable|string',
            'body' => 'nullable|string',
            'video_url' => 'nullable|string',
        ]);

        $lesson->update($validated);

        return redirect()->back()->with('success', 'បានកែប្រែមេរៀនដោយជោគជ័យ។');
    }

    public function destroy(ContentLesson $lesson)
    {
        $lesson->delete();

        return redirect()->back()->with('success', 'បានលុបមេរៀនដោយជោគជ័យ។');
    }

    private function metadata(Request $request, array $validated, ?string $thumbnailPath, ?string $videoUploadPath, ?string $videoThumbnailPath, ?string $documentPath, array $attachmentFiles): array
    {
        return [
            'thumbnail' => $thumbnailPath,
            'learning' => [
                'is_required' => $request->boolean('is_required'),
                'track_progress' => $request->boolean('track_progress'),
                'auto_complete' => $request->boolean('auto_complete'),
                'unlock_next' => $request->boolean('unlock_next'),
                'completion_type' => $validated['completion_type'],
                'minimum_watch_percentage' => $validated['minimum_watch_percentage'] ?? null,
                'require_watch_before_completion' => $request->boolean('require_watch_before_completion'),
                'prevent_skipping' => $request->boolean('prevent_skipping'),
                'auto_mark_completed' => $request->boolean('auto_mark_completed'),
            ],
            'video' => [
                'source' => $validated['video_source'] ?? null,
                'url' => $validated['video_url'] ?? null,
                'upload_path' => $videoUploadPath,
                'thumbnail_path' => $videoThumbnailPath,
                'transcript' => $request->input('video_transcript'),
            ],
            'quiz' => $request->input('quiz', []),
            'assignment' => $request->input('assignment', []),
            'document' => array_merge($request->input('document', []), ['path' => $documentPath]),
            'attachments' => $attachmentFiles,
            'access' => $request->input('access', []),
            'publishing' => [
                'status' => $validated['status'],
                'publish_date' => $validated['publish_date'] ?? null,
                'publish_time' => $validated['publish_time'] ?? null,
                'published_by' => $request->input('published_by'),
            ],
        ];
    }

    private function uniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'content';
        $slug = $baseSlug;
        $count = 1;

        while (ContentLesson::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $count++;
        }

        return $slug;
    }
}
