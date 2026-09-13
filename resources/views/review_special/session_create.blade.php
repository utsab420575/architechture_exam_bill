@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Add Session</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{ route('dashboard') }}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>Session</span></li>
                    <li><span>Add</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        {{--content start--}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- end timeline content-->

                        <div class="tab-pane" id="settings">
                            <form id="myForm" method="post" action="{{ route('session.store') }}" enctype="multipart/form-data">
                                @csrf

                                <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> Add Session</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="session" class="form-label">Academic Session</label>
                                            <input type="text" name="session" id="session" class="form-control" placeholder="e.g. 2023-2024">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="year" class="form-label">Year</label>
                                            <input type="text" name="year" id="year" class="form-control" placeholder="e.g. 1 or 2">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="semester" class="form-label">Semester</label>
                                            <input type="text" name="semester" id="semester" class="form-control" placeholder="e.g. 1 or 2">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="exam_type_id" class="form-label">Exam Type</label>
                                            <select name="exam_type_id" id="exam_type_id" class="form-select">
                                                <option selected disabled>Select Type</option>
                                                <option value="1"> Regular</option>
                                                <option value="2"> Review</option>
                                                <option value="3"> Special</option>
                                            </select>
                                        </div>
                                    </div>
                                    {{-- No field for ugr_id; it will be NULL --}}
                                </div> <!-- end row -->

                                <div class="text-end">
                                    <button type="submit" class="btn btn-success waves-effect waves-light mt-2">
                                        <i class="mdi mdi-content-save"></i> Save
                                    </button>
                                </div>
                            </form>
                        </div>
                        <!-- end settings content-->

                    </div>
                </div> <!-- end card-->

            </div> <!-- end col -->
        </div>
        <!-- end row-->

    </section>
@endsection

@push('scripts')
@endpush
