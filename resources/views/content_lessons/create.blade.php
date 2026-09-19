@extends('layouts.master')

@section('title', 'Create Lesson | LMS')

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/plugins/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/dist/css/lesson-studio.css') }}">
@endpush

@php
    // Content types offered by this form -> stored `content_type` value.
    $types = [
        'video'  => ['label' => 'Video',         'icon' => 'video'],
        'lesson' => ['label' => 'Article',       'icon' => 'book-open'],
        'file'   => ['label' => 'PDF',           'icon' => 'file-text'],
        'url'    => ['label' => 'External Link', 'icon' => 'external-link'],
    ];
    $type = old('content_type', 'video');
    $type = array_key_exists($type, $types) ? $type : 'video';

    $manual = 'Mark as Complete (manual)';
    $completionOptions = [
        'video'  => ['video_watched' => 'Video Watched', 'manual' => $manual],
        'lesson' => ['article_read' => 'Mark as Read', 'manual' => $manual],
        'file'   => ['pdf_viewed' => 'PDF Viewed', 'manual' => $manual],
        'url'    => ['link_visited' => 'Link Visited', 'manual' => $manual],
    ];

    $moduleLabel = fn ($m) => \Illuminate\Support\Str::startsWith(\Illuminate\Support\Str::lower($m->title), 'module')
        ? $m->title
        : 'Module ' . $m->module_number . ': ' . $m->title;

    $courseId = old('course_id', $selectedCourse?->course_id);
    $moduleId = old('course_module_id', $selectedModule?->course_module_id);
    $nextPosition = $selectedModule ? $selectedModule->lessons_count + 1 : 1;
    $status = old('status', 'draft');
    $minutes = old('video_duration');

    // Data handed to lesson-studio.js (kept here: @json() can't parse multi-line closures).
    $modulesJs = $modules->map(fn ($m) => [
        'id' => $m->course_module_id,
        'course_id' => $m->course_id,
        'label' => $moduleLabel($m),
        'next_position' => $m->lessons_count + 1,
    ])->values();
    $coursesJs = $courses->mapWithKeys(fn ($c) => [$c->course_id => $c->course_name]);

    $cancelUrl = $selectedCourse ? route('courses.modules.index', $selectedCourse) : route('courses.index');
    $videoAccept = 'video/mp4,video/quicktime,video/x-msvideo,video/webm';
@endphp

@section('content')
    {{-- Lucide icon sprite, inlined once so <use href="#name"> works for server- and JS-rendered icons. --}}
    {!! file_get_contents(public_path('backend/dist/img/lucide-sprite.svg')) !!}

    <div class="lesson-studio">
        <nav class="ls-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('courses.index') }}">Courses</a>
            <x-lesson.icon name="chevron-right" />
            <span id="crumbCourse" class="ls-crumb-truncate">{{ $selectedCourse?->course_name ?? 'Select a course' }}</span>
            <x-lesson.icon name="chevron-right" />
            <span id="crumbModule" class="ls-crumb-truncate">{{ $selectedModule ? $moduleLabel($selectedModule) : 'Select a module' }}</span>
            <x-lesson.icon name="chevron-right" />
            <strong>Create Lesson</strong>
        </nav>

        <div class="ls-toasts" id="toasts" aria-live="polite"></div>

        <form id="lessonForm" action="{{ route('lessons.store') }}" method="POST" enctype="multipart/form-data" novalidate
            data-loading-text="កំពុងរក្សាទុក...">
            @csrf

            <div class="ls-layout">
                {{-- ================= MAIN COLUMN (70%) ================= --}}
                <div class="ls-main">
                    <x-lesson.card title="Create New Lesson" subtitle="Add learning content to a module of your course." icon="pen-line">
                        {{-- Course / Module --}}
                        <div class="ls-grid-2 ls-context">
                            <x-lesson.select name="course_id" label="Course" :required="true" :value="$courseId" id="courseSelect">
                                <option value="">Select a course</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->course_id }}" @selected((string) $courseId === (string) $course->course_id)>
                                        {{ $course->course_name }}
                                    </option>
                                @endforeach
                            </x-lesson.select>

                            <x-lesson.select name="course_module_id" label="Module" :required="true" :value="$moduleId" id="moduleSelect">
                                <option value="">Select a module</option>
                                @foreach ($modules as $module)
                                    <option value="{{ $module->course_module_id }}" data-course="{{ $module->course_id }}"
                                        @selected((string) $moduleId === (string) $module->course_module_id)>
                                        {{ $moduleLabel($module) }}
                                    </option>
                                @endforeach
                            </x-lesson.select>
                        </div>

                        {{-- Title --}}
                        <x-lesson.input name="title" label="Lesson Title" :required="true" id="titleInput" maxlength="180"
                            placeholder="e.g. Introduction to Docker multi-stage builds" autocomplete="off" />

                        {{-- Slug --}}
                        <x-lesson.field name="slug" label="Slug" for="slugInput" help="Leave empty to generate it from the title.">
                            <div class="ls-slug">
                                <span class="ls-prefix">lms.spi.edu.kh/lessons/</span>
                                <input type="text" name="slug" id="slugInput" value="{{ old('slug') }}" placeholder="auto-generated-slug"
                                    autocomplete="off" aria-invalid="{{ $errors->has('slug') ? 'true' : 'false' }}"
                                    class="ls-control {{ $errors->has('slug') ? 'is-invalid' : '' }}">
                                <span class="ls-badge ls-badge-muted" id="slugStatus">Auto</span>
                            </div>
                        </x-lesson.field>

                        {{-- Content type --}}
                        <div class="ls-field">
                            <label id="typeLabel">Content Type</label>
                            <div class="ls-segmented" role="radiogroup" aria-labelledby="typeLabel">
                                @foreach ($types as $value => $meta)
                                    <label class="ls-seg {{ $type === $value ? 'active' : '' }}">
                                        <input type="radio" class="ls-sr" name="content_type" value="{{ $value }}" @checked($type === $value)>
                                        <x-lesson.icon :name="$meta['icon']" />
                                        <span>{{ $meta['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('content_type')
                                <p class="ls-error" role="alert"><x-lesson.icon name="circle-alert" /> <span>{{ $message }}</span></p>
                            @enderror
                        </div>

                        {{-- ---------- VIDEO ---------- --}}
                        <div class="ls-pane" data-pane="video">
                            <div class="ls-grid-2">
                                <x-lesson.select name="video_source" label="Video Source" id="videoSource" value="youtube" :options="[
                                    'youtube' => 'YouTube',
                                    'vimeo' => 'Vimeo',
                                    'external' => 'Direct video URL',
                                    'upload' => 'Upload a file',
                                ]" />
                                <div data-source="url">
                                    <x-lesson.input name="video_url" type="url" label="Video URL" :required="true" id="videoUrl"
                                        placeholder="https://youtube.com/watch?v=..." autocomplete="off" />
                                </div>
                            </div>

                            <div data-source="upload" hidden>
                                <x-lesson.dropzone name="video_upload" label="Video File" :required="true" :accept="$videoAccept"
                                    icon="video" hint="MP4, MOV, AVI or WEBM · up to 500 MB" data-max-mb="500" kind="video" />
                            </div>

                            <div class="ls-grid-2 ls-video-row">
                                <x-lesson.dropzone name="video_thumbnail" label="Thumbnail" :optional="true" accept="image/*"
                                    icon="image" title="Drag & Drop Image" hint="JPG, PNG or WEBP · up to 4 MB" data-max-mb="4" kind="thumb" />
                                <div class="ls-field">
                                    <label>Preview</label>
                                    <div class="ls-video-preview" id="videoPreview">
                                        <div class="ls-video-empty">
                                            <x-lesson.icon name="circle-play" />
                                            <span>Paste a video URL to preview it here</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ---------- ARTICLE ---------- --}}
                        <div class="ls-pane" data-pane="lesson" hidden>
                            <x-lesson.field name="body" label="Lesson Content" :required="true">
                                <div class="ls-editor is-loading" id="editorShell">
                                    <textarea name="body" id="bodyEditor">{{ old('body') }}</textarea>
                                </div>
                            </x-lesson.field>
                        </div>

                        {{-- ---------- PDF ---------- --}}
                        <div class="ls-pane" data-pane="file" hidden>
                            <x-lesson.dropzone name="document[document_file]" label="PDF File" :required="true"
                                accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip" icon="file-text"
                                hint="PDF recommended · DOC, PPT, XLS or ZIP also accepted · up to 100 MB" data-max-mb="100" kind="document" />
                        </div>

                        {{-- ---------- EXTERNAL LINK ---------- --}}
                        <div class="ls-pane" data-pane="url" hidden>
                            <div class="ls-grid-2">
                                <x-lesson.input name="external_url" type="url" label="URL" :required="true" id="externalUrl"
                                    placeholder="https://meet.google.com/abc-defg-hij" autocomplete="off" />
                                <x-lesson.input name="external_title" label="Link Title" :optional="true" maxlength="180"
                                    placeholder="e.g. Live session on Google Meet" />
                            </div>
                        </div>

                        {{-- Description (all types) --}}
                        <x-lesson.input name="summary" label="Description" :optional="true" :textarea="true" :rows="3" maxlength="1000"
                            placeholder="A short summary of what students will learn in this lesson." />

                        <div class="ls-grid-2">
                            <x-lesson.field name="video_duration" label="Duration" :optional="true" for="durationValue">
                                <div class="ls-duration">
                                    <input type="number" id="durationValue" class="ls-control" min="0" step="1" placeholder="15"
                                        value="{{ $minutes }}" inputmode="numeric">
                                    <select id="durationUnit" class="ls-control ls-select" aria-label="Duration unit">
                                        <option value="minutes">Minutes</option>
                                        <option value="hours">Hours</option>
                                    </select>
                                </div>
                                <input type="hidden" name="video_duration" id="durationMinutes" value="{{ $minutes }}">
                            </x-lesson.field>

                            <x-lesson.input name="position" type="number" label="Order" :required="true" :value="$nextPosition" min="1"
                                step="1" id="positionInput" help="Controls the position of this lesson inside the module." />
                        </div>
                    </x-lesson.card>

                    <x-lesson.card title="Attachments" subtitle="Extra files students can download with this lesson." icon="paperclip">
                        <x-lesson.dropzone name="attachments[]" title="Drag & Drop Files" :multiple="true" icon="folder-open"
                            hint="Any file type · up to 100 MB each" data-max-mb="100" kind="attachments" />
                        @foreach ($errors->get('attachments.*') as $messages)
                            <p class="ls-error" role="alert"><x-lesson.icon name="circle-alert" /> <span>{{ $messages[0] }}</span></p>
                        @endforeach
                    </x-lesson.card>
                </div>

                {{-- ================= SIDEBAR (30%) ================= --}}
                <aside class="ls-side" aria-label="Lesson settings">
                    <x-lesson.card title="Lesson Settings" icon="settings">
                        <div class="ls-field">
                            <label id="statusLabel">Status</label>
                            <div class="ls-segmented ls-segmented-sm" role="radiogroup" aria-labelledby="statusLabel">
                                <label class="ls-seg {{ $status === 'draft' ? 'active' : '' }}">
                                    <input type="radio" class="ls-sr" name="status" value="draft" @checked($status === 'draft')>
                                    <span>Draft</span>
                                </label>
                                <label class="ls-seg {{ $status === 'published' ? 'active' : '' }}">
                                    <input type="radio" class="ls-sr" name="status" value="published" @checked($status === 'published')>
                                    <span>Published</span>
                                </label>
                            </div>
                            <p class="ls-help" id="statusHelp">Draft lessons are hidden from students.</p>
                            @error('status')
                                <p class="ls-error" role="alert"><x-lesson.icon name="circle-alert" /> <span>{{ $message }}</span></p>
                            @enderror
                        </div>
                    </x-lesson.card>

                    <x-lesson.card title="Progress" icon="target">
                        <div class="ls-switches">
                            <x-lesson.switch name="track_progress" label="Track Progress" desc="Count this lesson in course progress." :checked="true" />
                            <x-lesson.switch name="is_required" label="Required Lesson" desc="Students must complete it." :checked="true" />
                            <x-lesson.switch name="auto_complete" label="Auto Complete" desc="Mark done when the requirement is met." />
                            <x-lesson.switch name="unlock_next" label="Unlock Next Lesson" desc="Open the next lesson on completion." :checked="true" />
                        </div>
                    </x-lesson.card>

                    <x-lesson.card title="Lesson Completion" icon="circle-check">
                        <x-lesson.select name="completion_type" label="Completion Method" id="completionType"
                            :options="$completionOptions[$type]" />
                        <x-lesson.input name="minimum_watch_percentage" type="number" label="Minimum Requirement" min="0" max="100"
                            :value="80" suffix="%" id="minWatch" help="Share of the video a student must watch." />
                    </x-lesson.card>
                </aside>
            </div>

            {{-- ================= FIXED ACTION BAR ================= --}}
            <div class="ls-bar" role="region" aria-label="Form actions">
                <div class="ls-bar-meta">
                    <span class="ls-dot" id="dirtyDot"></span>
                    <span id="dirtyText">No changes yet</span>
                </div>
                <div class="ls-bar-actions">
                    <a href="{{ $cancelUrl }}" class="ls-btn ls-btn-ghost">Cancel</a>
                    <button type="submit" class="ls-btn ls-btn-secondary" data-force-status="draft">
                        <x-lesson.icon name="save" /> <span>Save as Draft</span>
                    </button>
                    <button type="submit" class="ls-btn ls-btn-primary">
                        <x-lesson.icon name="check" /> <span>Create Lesson</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        window.lessonStudio = {
            modules: @json($modulesJs),
            courses: @json($coursesJs),
            existingSlugs: @json($existingSlugs),
            completion: @json($completionOptions),
            oldCompletion: @json(old('completion_type')),
            flash: {
                success: @json(session('success')),
                errorCount: {{ count($errors->keys()) }},
            },
        };
    </script>
    <script src="{{ asset('backend/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('backend/dist/js/lesson-studio.js') }}"></script>
@endpush
