@extends('layouts.master')

@section('title', $lesson->title . ' | LMS')

@section('content')
<section class="content-header px-0">
    <div class="container-fluid px-0">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-7">
                <h1 class="mb-1">{{ $lesson->title }}</h1>
                <p class="text-muted mb-0">
                    ម៉ូឌុល៖ <strong>{{ $lesson->courseModule?->title ?? 'N/A' }}</strong> 
                    | វគ្គសិក្សា៖ <strong>{{ $lesson->course?->course_name ?? 'N/A' }}</strong>
                </p>
            </div>
            <div class="col-sm-5">
                <ol class="breadcrumb float-sm-right mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">ផ្ទាំងគ្រប់គ្រង</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">វគ្គសិក្សា</a></li>
                    @if($lesson->course)
                        <li class="breadcrumb-item"><a href="{{ route('courses.modules.index', $lesson->course_id) }}">ម៉ូឌុល</a></li>
                    @endif
                    <li class="breadcrumb-item active">មេរៀន</li>
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

<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">
            <i class="fas fa-book-open text-primary mr-2"></i> ព័ត៌មានលម្អិតមេរៀន
        </h3>
        <div class="ml-auto">
            @if($lesson->course_id)
                <a href="{{ route('courses.modules.index', $lesson->course_id) }}" class="btn btn-secondary btn-sm mr-1">
                    <i class="fas fa-arrow-left mr-1"></i> ត្រឡប់ក្រោយ
                </a>
            @endif
            @auth
                <form action="{{ route('lessons.destroy', $lesson) }}" method="POST" class="d-inline" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបមេរៀននេះមែនទេ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash mr-1"></i> លុប
                    </button>
                </form>
            @endauth
        </div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <span class="badge badge-primary px-3 py-2" style="font-size: 14px;">
                <i class="fas fa-tag mr-1"></i> {{ ucfirst($lesson->content_type) }}
            </span>
            @if($lesson->duration_minutes)
                <span class="badge badge-info px-3 py-2 ml-2" style="font-size: 14px;">
                    <i class="fas fa-clock mr-1"></i> {{ $lesson->duration_minutes }} នាទី
                </span>
            @endif
        </div>

        @if($lesson->summary)
            <div class="callout callout-info mb-4">
                <h5 class="text-info font-weight-bold"><i class="fas fa-info-circle mr-1"></i> សេចក្តីសង្ខេប</h5>
                <p class="mb-0">{!! nl2br(e($lesson->summary)) !!}</p>
            </div>
        @endif

        @if($lesson->video_url)
            <div class="mb-4">
                <h5 class="font-weight-bold mb-2"><i class="fas fa-video text-danger mr-1"></i> វីដេអូ</h5>
                <div class="embed-responsive embed-responsive-16by9 rounded border shadow-sm">
                    @if(Str::contains($lesson->video_url, ['youtube.com', 'youtu.be']))
                        @php
                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $lesson->video_url, $matches);
                            $youtubeId = $matches[1] ?? null;
                        @endphp
                        @if($youtubeId)
                            <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{ $youtubeId }}" allowfullscreen></iframe>
                        @else
                            <a href="{{ $lesson->video_url }}" target="_blank" class="p-4 d-block bg-light text-center">{{ $lesson->video_url }}</a>
                        @endif
                    @else
                        <a href="{{ $lesson->video_url }}" target="_blank" class="p-4 d-block bg-light text-center">
                            <i class="fas fa-external-link-alt mr-1"></i> មើលវីដេអូ៖ {{ $lesson->video_url }}
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if($lesson->body)
            <div class="mb-4">
                <h5 class="font-weight-bold mb-2"><i class="fas fa-file-alt text-secondary mr-1"></i> ខ្លឹមសារមេរៀន</h5>
                <div class="p-3 bg-light rounded border">
                    {!! nl2br(e($lesson->body)) !!}
                </div>
            </div>
        @endif

        @if($lesson->file_path)
            <div class="mb-4">
                <h5 class="font-weight-bold mb-2"><i class="fas fa-paperclip text-success mr-1"></i> ឯកសារភ្ជាប់</h5>
                <a href="{{ asset('storage/' . $lesson->file_path) }}" target="_blank" class="btn btn-outline-success">
                    <i class="fas fa-download mr-1"></i> ទាញយកឯកសារ
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
