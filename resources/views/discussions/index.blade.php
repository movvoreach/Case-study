@extends('layouts.master')

@section('title', 'វេទិកាពិភាក្សា (Discussion Forum) | LMS')

@section('content')
<section class="content-header px-0">
    <div class="container-fluid px-0">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-7">
                <h1 class="mb-1 font-weight-bold">
                    <i class="fas fa-comments text-primary mr-2"></i>
                    វេទិកាពិភាក្សា និងផ្លាស់ប្តូរយោបល់ (Discussion Forum)
                </h1>
                <p class="text-muted mb-0">កន្លែងសាកសួរ ដោះស្រាយចម្ងល់ និងសហការរវាងគ្រូ និងនិស្សិត។</p>
            </div>
            <div class="col-sm-5">
                <ol class="breadcrumb float-sm-right mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">ផ្ទាំងគ្រប់គ្រង</a></li>
                    <li class="breadcrumb-item active">វេទិកាពិភាក្សា</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Forum Header Actions Bar -->
<div class="card shadow-sm border-0 rounded-lg mb-4">
    <div class="card-body py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-primary px-3 py-2 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-fire mr-1"></i> ប្រធានបទក្តៅៗ
            </span>
            <span class="text-muted small">ប្រធានបទសរុប: <strong>{{ $discussions->count() }}</strong></span>
        </div>

        <button type="button" class="btn btn-primary btn-sm px-3 font-weight-bold" data-toggle="modal" data-target="#newTopicModal">
            <i class="fas fa-plus-circle mr-1"></i>
            បង្កើតប្រធានបទពិភាក្សាថ្មី
        </button>
    </div>
</div>

<div class="row">
    <!-- Left Forum Topics Column -->
    <div class="col-lg-8">
        @foreach($discussions as $topic)
            <div class="card shadow-sm border-0 rounded-lg mb-3 hover-shadow transition-all" style="border-left: 4px solid {{ $topic['is_pinned'] ? '#2563eb' : '#e2e8f0' }} !important;">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start gap-3">
                        <img src="{{ asset('backend/dist/img/' . $topic['author_avatar']) }}" class="rounded-circle border mr-3" width="45" height="45" style="object-fit:cover;">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                @if($topic['is_pinned'])
                                    <span class="badge badge-danger px-2 py-0.5" style="font-size: 11px;"><i class="fas fa-thumbtack mr-1"></i>ខ្ទាស់</span>
                                @endif
                                <span class="badge badge-light border text-primary font-weight-bold px-2 py-0.5" style="font-size: 11px;">
                                    {{ $topic['course_name'] }}
                                </span>
                                <small class="text-muted ml-auto"><i class="fas fa-clock mr-1"></i>{{ $topic['last_activity'] }}</small>
                            </div>
                            <h5 class="font-weight-bold mb-1">
                                <a href="#" class="text-dark text-decoration-none hover-primary">{{ $topic['title'] }}</a>
                            </h5>
                            <div class="d-flex align-items-center text-muted small gap-3 mt-2">
                                <span><i class="fas fa-user-circle mr-1 text-secondary"></i>ដោយ: <strong>{{ $topic['author_name'] }}</strong></span>
                                <span class="ml-3"><i class="fas fa-comment-dots mr-1 text-info"></i>{{ $topic['replies_count'] }} ចម្លើយ</span>
                                <span class="ml-3"><i class="fas fa-eye mr-1 text-muted"></i>{{ $topic['views_count'] }} ទស្សនា</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Right Sidebar Info Column -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-lg mb-4">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold mb-0 text-dark">
                    <i class="fas fa-filter text-primary mr-2"></i>
                    ចម្រាញ់តាមវគ្គសិក្សា
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3 bg-light font-weight-bold text-primary">
                        <span><i class="fas fa-layer-group mr-2"></i>គ្រប់វគ្គសិក្សាទាំងអស់</span>
                        <span class="badge badge-primary badge-pill px-2.5 py-1">{{ $discussions->count() }}</span>
                    </li>
                    @foreach($courses as $c)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                            <span class="text-dark small"><i class="fas fa-book mr-2 text-muted"></i>{{ $c->course_name }}</span>
                            <span class="badge badge-light border badge-pill px-2.5 py-1 text-muted">{{ rand(2, 8) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- New Topic Modal -->
<div class="modal fade" id="newTopicModal" tabindex="-1" role="dialog" aria-labelledby="newTopicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="newTopicModalLabel">
                    <i class="fas fa-comments mr-2"></i>
                    បង្កើតប្រធានបទពិភាក្សាថ្មី
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('dashboard') }}" method="GET" onsubmit="alert('ប្រធានបទពិភាក្សាត្រូវបានបង្កើតដោយជោគជ័យ!'); $('#newTopicModal').modal('hide'); return false;">
                <div class="modal-body">
                    <div class="form-group">
                        <label>វគ្គសិក្សា (Course) <span class="text-danger">*</span></label>
                        <select class="form-control select2bs4" style="width:100%;" required>
                            <option value="">-- ជ្រើសរើសវគ្គសិក្សា --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->course_id }}">{{ $c->course_name }} ({{ $c->course_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>ចំណងជើងប្រធានបទ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="បញ្ចូលចំណងជើងសាកសួរ..." required>
                    </div>

                    <div class="form-group">
                        <label>ខ្លឹមសារសាកសួរ ឬពិភាក្សា <span class="text-danger">*</span></label>
                        <textarea class="form-control" rows="5" placeholder="ពិពណ៌នាអំពីចម្ងល់របស់អ្នកឱ្យបានច្បាស់លាស់..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-paper-plane mr-1"></i> បោះពុម្ពប្រធានបទ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
