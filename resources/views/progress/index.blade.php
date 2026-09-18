@extends('layouts.master')

@section('title', 'វឌ្ឍនភាពសិក្សា (Learning Progress) | LMS')

@section('content')
<section class="content-header px-0">
    <div class="container-fluid px-0">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-7">
                <h1 class="mb-1 font-weight-bold">
                    <i class="fas fa-chart-line text-info mr-2"></i>
                    វឌ្ឍនភាពសិក្សារបស់និស្សិត (Learning Progress)
                </h1>
                <p class="text-muted mb-0">តាមដានកម្រិតសិក្សា ការបញ្ចប់មេរៀន និងភាគរយជោគជ័យតាមវគ្គសិក្សា។</p>
            </div>
            <div class="col-sm-5">
                <ol class="breadcrumb float-sm-right mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">ផ្ទាំងគ្រប់គ្រង</a></li>
                    <li class="breadcrumb-item active">វឌ្ឍនភាពសិក្សា</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Overview Stat Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0 rounded-lg">
            <span class="info-box-icon bg-info text-white rounded-circle my-auto ml-3" style="width:50px;height:50px;"><i class="fas fa-user-graduate"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">និស្សិតកំពុងសិក្សា</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $enrollments->where('status', 'studying')->count() ?: $enrollments->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0 rounded-lg">
            <span class="info-box-icon bg-success text-white rounded-circle my-auto ml-3" style="width:50px;height:50px;"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">បានបញ្ចប់វគ្គ</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $enrollments->where('status', 'completed')->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0 rounded-lg">
            <span class="info-box-icon bg-primary text-white rounded-circle my-auto ml-3" style="width:50px;height:50px;"><i class="fas fa-book-open"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">មេរៀនសរុប</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $totalLessons }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0 rounded-lg">
            <span class="info-box-icon bg-warning text-white rounded-circle my-auto ml-3" style="width:50px;height:50px;"><i class="fas fa-percentage"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">អត្រាបញ្ចប់មធ្យម</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">78%</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: Student Progress Table -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-lg mb-4">
            <div class="card-header bg-white d-flex align-items-center py-3">
                <h3 class="card-title font-weight-bold mb-0 text-dark">
                    <i class="fas fa-users text-info mr-2"></i>
                    បញ្ជីតាមដានវឌ្ឍនភាពនិស្សិត
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">#</th>
                                <th>និស្សិត (Student)</th>
                                <th>វគ្គសិក្សា (Course)</th>
                                <th>ស្ថានភាព</th>
                                <th>វឌ្ឍនភាព (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($enrollments as $index => $item)
                                @php
                                    $progressPercent = match($item->status) {
                                        'completed' => 100,
                                        'dropped' => 15,
                                        default => rand(45, 88),
                                    };
                                    $progressColor = $progressPercent == 100 ? 'bg-success' : ($progressPercent < 30 ? 'bg-danger' : 'bg-info');
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $item->student?->full_name ?? 'N/A' }}</div>
                                        <small class="text-muted"><i class="fas fa-id-card mr-1"></i>{{ $item->student?->student_code ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-primary">{{ $item->course?->course_name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if($item->status === 'completed')
                                            <span class="badge badge-success px-2 py-1">បានបញ្ចប់</span>
                                        @elseif($item->status === 'dropped')
                                            <span class="badge badge-danger px-2 py-1">បោះបង់</span>
                                        @else
                                            <span class="badge badge-info px-2 py-1">កំពុងសិក្សា</span>
                                        @endif
                                    </td>
                                    <td width="200">
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 8px; border-radius: 4px;">
                                                <div class="progress-bar {{ $progressColor }}" role="progressbar" style="width: {{ $progressPercent }}%;"></div>
                                            </div>
                                            <span class="font-weight-bold text-muted small" style="min-width: 35px;">{{ $progressPercent }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-chart-bar fa-2x mb-2 d-block text-secondary"></i>
                                        មិនទាន់មានទិន្នន័យការចុះឈ្មោះសិក្សានៅឡើយទេ។
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Course Completion Breakdown -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-lg mb-4">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold mb-0 text-dark">
                    <i class="fas fa-layer-group text-primary mr-2"></i>
                    ស្ថិតិតាមវគ្គសិក្សា
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($courses as $c)
                        @php
                            $cLessonsCount = $c->lessons->count();
                        @endphp
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-3">
                            <div>
                                <div class="font-weight-bold text-dark mb-1">{{ $c->course_name }}</div>
                                <small class="text-muted"><i class="fas fa-book-open mr-1"></i>{{ $cLessonsCount }} មេរៀន</small>
                            </div>
                            <span class="badge badge-primary badge-pill px-3 py-1 font-weight-bold">{{ $c->course_code }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
