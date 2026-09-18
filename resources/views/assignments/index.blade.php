@extends('layouts.master')

@section('title', 'កិច្ចការ (Assignments) | LMS')

@section('content')
<section class="content-header px-0">
    <div class="container-fluid px-0">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-7">
                <h1 class="mb-1 font-weight-bold">
                    <i class="fas fa-tasks text-success mr-2"></i>
                    កិច្ចការ និងលំហាត់ (Assignments & Homework)
                </h1>
                <p class="text-muted mb-0">គ្រប់គ្រងកិច្ចការ ថ្ងៃកំណត់ប្រគល់ និងការដាក់ពិន្ទុសម្រាប់និស្សិត។</p>
            </div>
            <div class="col-sm-5">
                <ol class="breadcrumb float-sm-right mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">ផ្ទាំងគ្រប់គ្រង</a></li>
                    <li class="breadcrumb-item active">កិច្ចការ</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Overview Stat Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0 rounded-lg">
            <span class="info-box-icon bg-success text-white rounded-circle my-auto ml-3" style="width:50px;height:50px;"><i class="fas fa-file-invoice"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">កិច្ចការសរុប</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $assignments->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0 rounded-lg">
            <span class="info-box-icon bg-primary text-white rounded-circle my-auto ml-3" style="width:50px;height:50px;"><i class="fas fa-upload"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">ការប្រគល់កិច្ចការ</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">Active</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0 rounded-lg">
            <span class="info-box-icon bg-warning text-white rounded-circle my-auto ml-3" style="width:50px;height:50px;"><i class="fas fa-star"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">ពិន្ទុអតិបរមា</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">100</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0 rounded-lg">
            <span class="info-box-icon bg-info text-white rounded-circle my-auto ml-3" style="width:50px;height:50px;"><i class="fas fa-book"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">វគ្គសិក្សាមានកិច្ចការ</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $assignments->pluck('course_id')->unique()->count() }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header bg-white d-flex align-items-center py-3">
        <h3 class="card-title font-weight-bold mb-0 text-dark">
            <i class="fas fa-list text-success mr-2"></i>
            បញ្ជីកិច្ចការសិក្សា
        </h3>
        <a href="{{ route('lessons.create') }}" class="btn btn-success btn-sm ml-auto font-weight-bold px-3">
            <i class="fas fa-plus mr-1"></i>
            បង្កើតកិច្ចការថ្មី
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="bg-light">
                    <tr>
                        <th width="50">#</th>
                        <th>ចំណងជើងកិច្ចការ</th>
                        <th>វគ្គសិក្សា (Course)</th>
                        <th>ម៉ូឌុល (Module)</th>
                        <th>ពិន្ទុអតិបរមា</th>
                        <th>កាលបរិច្ឆេទកំណត់</th>
                        <th>ស្ថានភាព</th>
                        <th width="120" class="text-center">សកម្មភាព</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $index => $item)
                        @php
                            $assignmentMeta = $item->metadata['assignment'] ?? [];
                            $maxScore = $assignmentMeta['maximum_score'] ?? ($item->max_score ?: 100);
                            $dueDate = $assignmentMeta['due_date'] ?? null;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $item->title }}</div>
                                <small class="text-muted"><i class="fas fa-file-alt mr-1"></i>{{ $item->summary ?: 'កិច្ចការអនុវត្ត' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-soft-success px-2 py-1 font-weight-bold text-success" style="background:#ecfdf5;">
                                    {{ $item->course?->course_name ?? 'មិនទាន់កំណត់' }}
                                </span>
                            </td>
                            <td>{{ $item->module_title ?: ($item->courseModule?->title ?? '-') }}</td>
                            <td>
                                <span class="badge badge-primary px-2 py-1 font-weight-bold">
                                    <i class="fas fa-star mr-1"></i> {{ $maxScore }} ពិន្ទុ
                                </span>
                            </td>
                            <td>
                                @if($dueDate)
                                    <span class="text-danger font-weight-bold"><i class="fas fa-clock mr-1"></i>{{ $dueDate }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_published)
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>បើកទទួល</span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1"><i class="fas fa-pause-circle mr-1"></i>សេចក្តីព្រាង</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('lessons.create', ['course_id' => $item->course_id, 'course_module_id' => $item->course_module_id]) }}" class="btn btn-sm btn-outline-success mr-1" title="កែប្រែ">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                                មិនទាន់មានទិន្នន័យកិច្ចការនៅឡើយទេ។ <a href="{{ route('lessons.create') }}">ចុចទីនេះដើម្បីបង្កើតថ្មី</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
