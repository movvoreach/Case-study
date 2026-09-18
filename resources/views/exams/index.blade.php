@extends('layouts.master')

@section('title', 'តេស្ត និងប្រឡង (Exams & Quizzes) | LMS')

@section('content')
<section class="content-header px-0">
    <div class="container-fluid px-0">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-7">
                <h1 class="mb-1 font-weight-bold">
                    <i class="fas fa-question-circle text-primary mr-2"></i>
                    តេស្ត និងប្រឡង (Exams & Quizzes)
                </h1>
                <p class="text-muted mb-0">គ្រប់គ្រងកម្រងសំណួរ តេស្ត និងការប្រឡងតាមដានសមត្ថភាពនិស្សិត។</p>
            </div>
            <div class="col-sm-5">
                <ol class="breadcrumb float-sm-right mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">ផ្ទាំងគ្រប់គ្រង</a></li>
                    <li class="breadcrumb-item active">តេស្ត និងប្រឡង</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Overview Stat Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0 rounded-lg">
            <span class="info-box-icon bg-primary text-white rounded-circle my-auto ml-3" style="width:50px;height:50px;"><i class="fas fa-clipboard-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">កម្រងសំណួរទាំងអស់</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $exams->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0 rounded-lg">
            <span class="info-box-icon bg-success text-white rounded-circle my-auto ml-3" style="width:50px;height:50px;"><i class="fas fa-check-double"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">បានបោះពុម្ពផ្សាយ</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $exams->where('is_published', true)->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0 rounded-lg">
            <span class="info-box-icon bg-warning text-white rounded-circle my-auto ml-3" style="width:50px;height:50px;"><i class="fas fa-award"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">ពិន្ទុជាប់មធ្យម</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">60%</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0 rounded-lg">
            <span class="info-box-icon bg-info text-white rounded-circle my-auto ml-3" style="width:50px;height:50px;"><i class="fas fa-layer-group"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">វគ្គសិក្សាមានតេស្ត</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $exams->pluck('course_id')->unique()->count() }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header bg-white d-flex align-items-center py-3">
        <h3 class="card-title font-weight-bold mb-0 text-dark">
            <i class="fas fa-list-alt text-primary mr-2"></i>
            បញ្ជីកម្រងសំណួរ និងការប្រឡង
        </h3>
        <a href="{{ route('lessons.create') }}" class="btn btn-primary btn-sm ml-auto font-weight-bold px-3">
            <i class="fas fa-plus mr-1"></i>
            បង្កើតកម្រងសំណួរថ្មី
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="bg-light">
                    <tr>
                        <th width="50">#</th>
                        <th>ចំណងជើងតេស្ត / ប្រឡង</th>
                        <th>វគ្គសិក្សា (Course)</th>
                        <th>ម៉ូឌុល (Module)</th>
                        <th>ចំនួនសំណួរ</th>
                        <th>ពិន្ទុជាប់</th>
                        <th>ស្ថានភាព</th>
                        <th width="120" class="text-center">សកម្មភាព</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $index => $exam)
                        @php
                            $questions = $exam->metadata['quiz']['questions'] ?? [];
                            $qCount = count($questions);
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $exam->title }}</div>
                                <small class="text-muted"><i class="fas fa-link mr-1"></i>{{ $exam->slug }}</small>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary px-2 py-1 font-weight-bold text-primary" style="background:#eff6ff;">
                                    {{ $exam->course?->course_name ?? 'មិនទាន់កំណត់' }}
                                </span>
                            </td>
                            <td>{{ $exam->module_title ?: ($exam->courseModule?->title ?? '-') }}</td>
                            <td>
                                <span class="badge badge-light border px-2 py-1 font-weight-bold">
                                    <i class="fas fa-question-circle text-info mr-1"></i> {{ $qCount }} សំណួរ
                                </span>
                            </td>
                            <td>
                                <span class="font-weight-bold text-success">{{ $exam->passing_score ?: 60 }}%</span>
                            </td>
                            <td>
                                @if($exam->is_published)
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>បោះពុម្ពផ្សាយ</span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1"><i class="fas fa-pen mr-1"></i>សេចក្តីព្រាង</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('lessons.create', ['course_id' => $exam->course_id, 'course_module_id' => $exam->course_module_id]) }}" class="btn btn-sm btn-outline-primary mr-1" title="កែប្រែ">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                                មិនទាន់មានទិន្នន័យកម្រងសំណួរ ឬការប្រឡងនៅឡើយទេ។ <a href="{{ route('lessons.create') }}">ចុចទីនេះដើម្បីបង្កើតថ្មី</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
