@extends('layouts.master')

@section('title', 'ម៉ូឌុល និងមេរៀន | LMS')

@section('content')
<section class="content-header px-0">
    <div class="container-fluid px-0">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-7">
                <h1 class="mb-1">ម៉ូឌុល និងមេរៀន</h1>
                <p class="text-muted mb-0">វគ្គសិក្សា៖ <strong>{{ $course->course_name }}</strong></p>
            </div>
            <div class="col-sm-5">
                <ol class="breadcrumb float-sm-right mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">ផ្ទាំងគ្រប់គ្រង</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">វគ្គសិក្សា</a></li>
                    <li class="breadcrumb-item active">ម៉ូឌុល</li>
                </ol>
            </div>
        </div>
    </div>
</section>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">បញ្ជីម៉ូឌុល</h3>
        @auth
            <button class="btn btn-primary btn-sm ml-auto" data-toggle="modal" data-target="#createModuleModal">
                <i class="fas fa-plus mr-1"></i> បង្កើតម៉ូឌុលថ្មី
            </button>
        @endauth
    </div>
    <div class="card-body">
        @if($modules->isEmpty())
            <div class="alert alert-info mb-0">មិនទាន់មានម៉ូឌុលនៅឡើយទេ។ សូមបង្កើតម៉ូឌុលថ្មីដើម្បីបន្ថែមមេរៀន។</div>
        @else
            <div class="accordion" id="modulesAccordion">
                @foreach($modules as $module)
                    <div class="card mb-2 shadow-sm">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap" id="heading{{ $module->course_module_id }}" style="background-color: #f8f9fa;">
                            <h5 class="mb-0">
                                <button class="btn btn-link text-dark font-weight-bold p-0 text-left" type="button" data-toggle="collapse" data-target="#collapse{{ $module->course_module_id }}" aria-expanded="true" aria-controls="collapse{{ $module->course_module_id }}">
                                    <i class="fas fa-folder text-primary mr-2"></i> ម៉ូឌុលទី {{ $module->module_number }}: {{ $module->title }}
                                </button>
                            </h5>
                            @auth
                                <div class="btn-group btn-group-sm mt-1 mt-md-0">
                                    <a href="{{ route('lessons.create', ['course_id' => $course->course_id, 'course_module_id' => $module->course_module_id]) }}" class="btn btn-success" title="បន្ថែមមេរៀន">
                                        <i class="fas fa-plus mr-1"></i> បន្ថែមមេរៀន
                                    </a>
                                    <button type="button" class="btn btn-warning btn-edit-module" 
                                            data-id="{{ $module->course_module_id }}" 
                                            data-number="{{ $module->module_number }}" 
                                            data-title="{{ $module->title }}" 
                                            data-description="{{ $module->description }}" 
                                            title="កែប្រែម៉ូឌុល">
                                        <i class="fas fa-edit"></i> កែប្រែ
                                    </button>
                                    <form action="{{ route('modules.destroy', $module->course_module_id) }}" method="POST" class="d-inline" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបម៉ូឌុលនេះ និងមេរៀនទាំងអស់ក្នុងម៉ូឌុលនេះមែនទេ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" title="លុបម៉ូឌុល">
                                            <i class="fas fa-trash"></i> លុប
                                        </button>
                                    </form>
                                </div>
                            @endauth
                        </div>

                        <div id="collapse{{ $module->course_module_id }}" class="collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading{{ $module->course_module_id }}" data-parent="#modulesAccordion">
                            <div class="card-body p-0">
                                @if($module->lessons->isEmpty())
                                    <div class="p-3 text-muted text-center border-top">មិនទាន់មានមាតិកានៅក្នុងម៉ូឌុលនេះទេ។</div>
                                @else
                                    <ul class="list-group list-group-flush">
                                        @foreach($module->lessons as $lesson)
                                            <li class="list-group-item d-flex align-items-center justify-content-between flex-wrap">
                                                <div class="d-flex align-items-center flex-grow-1 mr-3 my-1">
                                                    <div class="mr-3 text-secondary" style="font-size: 1.2rem;">
                                                        @if($lesson->content_type == 'video')
                                                            <i class="fas fa-video text-danger"></i>
                                                        @elseif($lesson->content_type == 'document' || $lesson->content_type == 'file')
                                                            <i class="fas fa-file-pdf text-warning"></i>
                                                        @elseif($lesson->content_type == 'assignment')
                                                            <i class="fas fa-tasks text-info"></i>
                                                        @else
                                                            <i class="fas fa-book-open text-primary"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <strong class="d-block">{{ $lesson->title }}</strong>
                                                        @if($lesson->summary)
                                                            <p class="mb-0 text-muted small">{{ Str::limit($lesson->summary, 100) }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center my-1">
                                                    <span class="badge badge-secondary mr-3">{{ ucfirst($lesson->content_type) }}</span>
                                                    
                                                    <div class="btn-group btn-group-sm">
                                                        <!-- View Lesson -->
                                                        <a href="{{ route('lessons.show', $lesson->content_lesson_id) }}" class="btn btn-info" title="មើលមេរៀន">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        @auth
                                                            <!-- Edit Lesson -->
                                                            <button type="button" class="btn btn-warning btn-edit-lesson" 
                                                                    data-id="{{ $lesson->content_lesson_id }}"
                                                                    data-title="{{ $lesson->title }}"
                                                                    data-type="{{ $lesson->content_type }}"
                                                                    data-summary="{{ $lesson->summary }}"
                                                                    data-body="{{ $lesson->body }}"
                                                                    data-video-url="{{ $lesson->video_url }}"
                                                                    title="កែប្រែមេរៀន">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                            <!-- Delete Lesson -->
                                                            <form action="{{ route('lessons.destroy', $lesson->content_lesson_id) }}" method="POST" class="d-inline" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបមេរៀននេះមែនទេ?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" title="លុបមេរៀន">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        @endauth
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@auth
<!-- Create Module Modal -->
<div class="modal fade" id="createModuleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('courses.modules.store', $course) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">បង្កើតម៉ូឌុលថ្មី</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>លេខរៀងម៉ូឌុល <span class="text-danger">*</span></label>
                        <input type="number" name="module_number" class="form-control" value="{{ $modules->max('module_number') + 1 }}" required min="1">
                    </div>
                    <div class="form-group">
                        <label>ចំណងជើង <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>ការពិពណ៌នា</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">បិទ</button>
                    <button type="submit" class="btn btn-primary">រក្សាទុក</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Module Modal -->
<div class="modal fade" id="editModuleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="editModuleForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">កែប្រែម៉ូឌុល</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>លេខរៀងម៉ូឌុល <span class="text-danger">*</span></label>
                        <input type="number" name="module_number" id="edit_module_number" class="form-control" required min="1">
                    </div>
                    <div class="form-group">
                        <label>ចំណងជើង <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit_module_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>ការពិពណ៌នា</label>
                        <textarea name="description" id="edit_module_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">បិទ</button>
                    <button type="submit" class="btn btn-primary">រក្សាទុក</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Lesson Modal -->
<div class="modal fade" id="editLessonModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="editLessonForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">កែប្រែមេរៀន</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>ចំណងជើងមាតិកា <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="edit_lesson_title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>ប្រភេទ <span class="text-danger">*</span></label>
                                <select name="content_type" id="edit_lesson_type" class="form-control" required>
                                    <option value="lesson">មេរៀន (Lesson)</option>
                                    <option value="video">វីដេអូ (Video)</option>
                                    <option value="file">ឯកសារ (File)</option>
                                    <option value="assignment">កិច្ចការ (Assignment)</option>
                                    <option value="quiz">កម្រងសំណួរ (Quiz)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>តំណភ្ជាប់វីដេអូ (ប្រសិនបើមាន)</label>
                        <input type="text" name="video_url" id="edit_lesson_video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                    </div>

                    <div class="form-group">
                        <label>សេចក្តីសង្ខេប</label>
                        <textarea name="summary" id="edit_lesson_summary" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label>ខ្លឹមសារលម្អិត</label>
                        <textarea name="body" id="edit_lesson_body" class="form-control" rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">បិទ</button>
                    <button type="submit" class="btn btn-primary">រក្សាទុក</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.btn-edit-module').click(function() {
            const id = $(this).data('id');
            const number = $(this).data('number');
            const title = $(this).data('title');
            const description = $(this).data('description');

            $('#editModuleForm').attr('action', "{{ url('modules') }}/" + id);
            $('#edit_module_number').val(number);
            $('#edit_module_title').val(title);
            $('#edit_module_description').val(description);

            $('#editModuleModal').modal('show');
        });

        $('.btn-edit-lesson').click(function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            const type = $(this).data('type');
            const summary = $(this).data('summary');
            const body = $(this).data('body');
            const videoUrl = $(this).data('video-url');

            $('#editLessonForm').attr('action', "{{ url('lessons') }}/" + id);
            $('#edit_lesson_title').val(title);
            $('#edit_lesson_type').val(type);
            $('#edit_lesson_summary').val(summary);
            $('#edit_lesson_body').val(body);
            $('#edit_lesson_video_url').val(videoUrl);

            $('#editLessonModal').modal('show');
        });

        @if ($errors->any())
            alert('មានបញ្ហាក្នុងការរក្សាទុក។ សូមពិនិត្យមើលទិន្នន័យរបស់អ្នក។\n\n{{ implode('\n', $errors->all()) }}');
        @endif
    });
</script>
@endpush
@endauth

@endsection
