@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Edit Session</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li><span>Session</span></li>
                    <li><span>Edit</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <div class="row">
            <div class="col-12">
                <section class="card">
                    <div class="card-body">
                        <div class="tab-pane" id="settings">
                            <form id="myForm" method="post" action="{{ route('session.update') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="{{ $session->id }}">

                                <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> Edit Session</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="session" class="form-label">Academic Session</label>
                                            <input type="text" name="session" id="session" class="form-control"
                                                   value="{{ old('session', $session->session) }}" placeholder="e.g. 2023-2024">
                                            @error('session') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="year" class="form-label">Year</label>
                                            <input type="text" name="year" id="year" class="form-control"
                                                   value="{{ old('year', $session->year) }}" placeholder="e.g. 1 or 2">
                                            @error('year') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="semester" class="form-label">Semester</label>
                                            <input type="text" name="semester" id="semester" class="form-control"
                                                   value="{{ old('semester', $session->semester) }}" placeholder="e.g. 1 or 2">
                                            @error('semester') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="exam_type_id" class="form-label">Exam Type</label>
                                            <select name="exam_type_id" id="exam_type_id" class="form-select">
                                                <option disabled>Select Type</option>
                                                @foreach($examTypes as $key => $label)
                                                    <option value="{{ $key }}" {{ (int)old('exam_type_id', $session->exam_type_id) === (int)$key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('exam_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    {{-- NEW: Status --}}
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select name="status" id="status" class="form-select">
                                                @php
                                                    $statusValue = (string) old('status', $session->status ?? 0);
                                                @endphp
                                                <option value="0" {{ $statusValue === '0' ? 'selected' : '' }}>Inactive (0)</option>
                                                <option value="1" {{ $statusValue === '1' ? 'selected' : '' }}>Active (1)</option>
                                            </select>
                                            @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    {{-- END Status --}}
                                </div> <!-- end row -->

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary mt-2">
                                        <i class="mdi mdi-content-save"></i> Update
                                    </button>
                                    <a href="{{ route('session.all') }}" class="btn btn-secondary mt-2">Back</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection
