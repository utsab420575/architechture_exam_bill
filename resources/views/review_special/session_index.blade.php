@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>All Sessions</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li><span>Session</span></li>
                    <li><span>All</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <div class="row">
            <div class="col-md-12">
                <section class="card">
                    <div class="card-body">
                        <div class="mb-3 text-end">
                            @can('session.add')
                                <a href="{{ route('session.add') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add Session
                                </a>
                            @endcan
                        </div>

                        <table class="table table-bordered table-striped mb-0" id="datatable-default">
                            <thead>
                            <tr>
                                <th>SL NO.</th>
                                <th>UGR ID</th>
                                <th>Academic Session</th>
                                <th>Year</th>
                                <th>Semester</th>
                                <th>Exam Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($sessions as $key => $single_session)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $single_session->ugr_id }}</td>
                                    <td>{{ $single_session->session }}</td>
                                    <td>{{ $single_session->year }}</td>
                                    <td>{{ $single_session->semester }}</td>
                                    <td>{{ $examTypes[$single_session->exam_type_id] ?? $single_session->exam_type_id }}</td>
                                    <td>
                                        @if ($single_session->status == 1)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @can('session.edit')
                                            <a href="{{ route('session.edit', $single_session->id) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                        @endcan
                                        @can('session.delete')
                                            <a href="{{ route('session.delete', $single_session->id) }}"
                                               class="btn btn-sm btn-danger delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection