@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Import Data</h2>

            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="index.html">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>

                    <li><span>Import</span></li>

                    <li><span>Tables</span></li>

                </ol>

                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <!-- start: page -->

        <div class="row">
            <div class="col-4">
                <h4 class="font-weight-bold text-dark">Select Table & Press Import</h4>
            </div>
            <div class="col-8">
                <h4 class="font-weight-bold text-dark text-center">Import Data Showing Section</h4>
            </div>

            <div class="col-md-4 ">
                <div class="accordion accordion-primary" id="parent">
                    {{--User--}}
                    <div class="card card-default">
                        <div class="card-header">
                            <h4 class="card-title m-0">
                                <a class="accordion-toggle" data-bs-toggle="collapse" data-bs-parent="#parent" data-bs-target="#child1">
                                    <span class="fa-stack fa-lg me-2">
                                      <i class="fa-solid fa-circle fa-stack-2x text-info"></i> <!-- Blue circle -->
                                      <i class="fa-solid fa-1 fa-stack-1x text-light" ></i>         <!-- White number -->
                                    </span>User Import
                                </a>
                            </h4>
                        </div>
                        <div id="child1" class="collapse show" data-bs-parent="#parent">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 text-start">
                                        {{-- Spinner Button (hidden by default) --}}
                                        <button class="btn btn-warning" type="button" id="loadingButton" disabled style="display: none;">
                                            <span class="spinner-grow spinner-grow-sm" aria-hidden="true"></span>
                                            <span role="status"> Importing...</span>
                                        </button>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <form method="POST" action="{{route('import.table.users')}}" id="importUserForm">
                                            @csrf
                                            <button type="submit" class="btn btn-warning" id="submitButton">
                                                <i class="fas fa-download"></i> Import Users
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{--Faculty--}}
                    <div class="card card-default">
                        <div class="card-header">
                            <h4 class="card-title m-0">
                                <a class="accordion-toggle" data-bs-toggle="collapse" data-bs-parent="#parent" data-bs-target="#child2">
                                    <span class="fa-stack fa-lg me-2">
                                      <i class="fa-solid fa-circle fa-stack-2x text-info"></i> <!-- Blue circle -->
                                      <i class="fa-solid fa-2 fa-stack-1x text-light" ></i>         <!-- White number -->
                                    </span>Faculty Import
                                </a>
                            </h4>
                        </div>
                        <div id="child2" class="collapse" data-bs-parent="#parent">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 text-start">
                                        {{-- Spinner Button (hidden by default) --}}
                                        <button class="btn btn-warning" type="button" id="loadingButtonFaculty" disabled style="display: none;">
                                            <span class="spinner-grow spinner-grow-sm" aria-hidden="true"></span>
                                            <span role="status"> Importing...</span>
                                        </button>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <form method="POST" action="{{route('import.table.faculties')}}" id="importFacultyForm">
                                            @csrf
                                            <button type="submit" class="btn btn-warning" id="submitButtonFaculty">
                                                <i class="fas fa-download"></i> Import Faculties
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{--Department--}}
                    <div class="card card-default">
                        <div class="card-header">
                            <h4 class="card-title m-0">
                                <a class="accordion-toggle" data-bs-toggle="collapse" data-bs-parent="#parent" data-bs-target="#child3">
                                    <span class="fa-stack fa-lg me-2">
                                      <i class="fa-solid fa-circle fa-stack-2x text-info"></i> <!-- Blue circle -->
                                      <i class="fa-solid fa-3 fa-stack-1x text-light" ></i>         <!-- White number -->
                                    </span>Department Import
                                </a>
                            </h4>
                        </div>
                        <div id="child3" class="collapse" data-bs-parent="#parent">
                            <div class="card-body text-end">
                                <div class="row">
                                    <div class="col-md-6 text-start">
                                        {{-- Spinner Button (hidden by default) --}}
                                        <button class="btn btn-warning" type="button" id="loadingButtonDepartment" disabled style="display: none;">
                                            <span class="spinner-grow spinner-grow-sm" aria-hidden="true"></span>
                                            <span role="status"> Importing...</span>
                                        </button>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <form method="POST" action="{{route('import.table.departments')}}" id="importDepartmentForm">
                                            @csrf
                                            <button type="submit" class="btn btn-warning" id="submitButtonDepartment">
                                                <i class="fas fa-download"></i> Import Departments
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{--Designation--}}
                    <div class="card card-default">
                        <div class="card-header">
                            <h4 class="card-title m-0">
                                <a class="accordion-toggle" data-bs-toggle="collapse" data-bs-parent="#parent" data-bs-target="#child4">
                                    <span class="fa-stack fa-lg me-2">
                                      <i class="fa-solid fa-circle fa-stack-2x text-info"></i> <!-- Blue circle -->
                                      <i class="fa-solid fa-4 fa-stack-1x text-light" ></i>         <!-- White number -->
                                    </span>Designation Import
                                </a>
                            </h4>
                        </div>
                        <div id="child4" class="collapse" data-bs-parent="#parent">
                            <div class="card-body text-end">
                                <div class="row">
                                    <div class="col-md-6 text-start">
                                        {{-- Spinner Button (hidden by default) --}}
                                        <button class="btn btn-warning" type="button" id="loadingButtonDesignation" disabled style="display: none;">
                                            <span class="spinner-grow spinner-grow-sm" aria-hidden="true"></span>
                                            <span role="status"> Importing...</span>
                                        </button>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <form method="POST" action="{{route('import.table.designations')}}" id="importDesignationForm">
                                            @csrf
                                            <button type="submit" class="btn btn-warning" id="submitButtonDesignation">
                                                <i class="fas fa-download"></i> Import Designation
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{--Teacher--}}
                    <div class="card card-default">
                        <div class="card-header">
                            <h4 class="card-title m-0">
                                <a class="accordion-toggle" data-bs-toggle="collapse" data-bs-parent="#parent" data-bs-target="#child5">
                                    <span class="fa-stack fa-lg me-2">
                                      <i class="fa-solid fa-circle fa-stack-2x text-info"></i> <!-- Blue circle -->
                                      <i class="fa-solid fa-5 fa-stack-1x text-light" ></i>         <!-- White number -->
                                    </span>Teacher Import
                                </a>
                            </h4>
                        </div>
                        <div id="child5" class="collapse" data-bs-parent="#parent">
                            <div class="card-body text-end">
                                <div class="row">
                                    <div class="col-md-6 text-start">
                                        {{-- Spinner Button (hidden by default) --}}
                                        <button class="btn btn-warning" type="button" id="loadingButtonTeacher" disabled style="display: none;">
                                            <span class="spinner-grow spinner-grow-sm" aria-hidden="true"></span>
                                            <span role="status"> Importing...</span>
                                        </button>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <form method="POST" action="{{route('import.table.teachers')}}" id="importTeacherForm">
                                            @csrf
                                            <button type="submit" class="btn btn-warning" id="submitButtonTeacher">
                                                <i class="fas fa-download"></i> Import Teachers
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-8 ">



                <section class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-striped mb-0" id="datatable-default">
                            <thead>
                            <tr>
                                <th>SL NO.</th>
                                <th>Table Name</th>
                                <th>Record Inserted</th>
                                <th>Record Updated</th>
                                <th>Import By Name</th>
                                <th>Import By Email</th>
                                <th>Created At</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($all_import_history as $key=>$single_history)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{$single_history->table_name}}</td>
                                        <td>{{$single_history->records_inserted}}</td>
                                        <td>{{$single_history->records_updated}}</td>
                                        <td>{{$single_history->imported_by_name}}</td>
                                        <td>{{$single_history->imported_by_email}}</td>
                                        <td>{{ $single_history->created_at->format('d-m-Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>

        </div>


        <!-- end: page -->
    </section>
@endsection
@push('scripts')
    <script>
        document.getElementById('importUserForm').addEventListener('submit', function () {
            document.getElementById('submitButton').style.display = 'none';
            document.getElementById('loadingButton').style.display = 'inline-block';
        });
    </script>

    <script>
        document.getElementById('importFacultyForm').addEventListener('submit', function () {
            document.getElementById('submitButtonFaculty').style.display = 'none';
            document.getElementById('loadingButtonFaculty').style.display = 'inline-block';
        });
    </script>

    <script>
        document.getElementById('importDepartmentForm').addEventListener('submit', function () {
            document.getElementById('submitButtonDepartment').style.display = 'none';
            document.getElementById('loadingButtonDepartment').style.display = 'inline-block';
        });
    </script>

    <script>
        document.getElementById('importDesignationForm').addEventListener('submit', function () {
            document.getElementById('submitButtonDesignation').style.display = 'none';
            document.getElementById('loadingButtonDesignation').style.display = 'inline-block';
        });
    </script>

    <script>
        document.getElementById('importTeacherForm').addEventListener('submit', function () {
            document.getElementById('submitButtonTeacher').style.display = 'none';
            document.getElementById('loadingButtonTeacher').style.display = 'inline-block';
        });
    </script>
@endpush
