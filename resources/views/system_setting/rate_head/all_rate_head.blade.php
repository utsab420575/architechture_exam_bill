@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>All Rate Heads</h2>

            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{ route('dashboard') }}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>System Setting</span></li>
                    <li><span>All Rate Heads</span></li>
                </ol>

                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <div class="row">
            <div class="col-md-12">
                <section class="card">
                    <header class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="card-title">Rate Head List</h2>
                        @can('rate_head.add')
                            <a href="{{ route('rate_head.add') }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-plus me-1"></i> Add Rate Head
                            </a>
                        @endcan
                    </header>
                    <div class="card-body">
                        <table class="table table-bordered table-striped mb-0" id="datatable-default">
                            <thead>
                            <tr>
                                <th>SL</th>
                                <th>Order No</th>
                                <th>Head</th>
                                <th>Sub Head</th>
                                <th>Dist Type</th>
                                <th>Min Rate</th>
                                <th>Max Rate</th>
                                <th>Course</th>
                                <th>Student Count</th>
                                <th>Merged With</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rate_heads as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td><span class="badge bg-dark">{{ $item->order_no }}</span></td>
                                    <td><strong>{{ $item->head }}</strong></td>
                                    <td>{{ $item->sub_head ?? '-' }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $item->dist_type }}</span></td>
                                    <td>
                                        @if($item->enable_min)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->enable_max)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->is_course)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->is_student_count)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->mergedWith ? $item->mergedWith->head : '-' }}</td>
                                    <td>
                                        @if($item->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @can('rate_head.edit')
                                            <a href="{{ route('rate_head.edit', $item->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                        @endcan
                                        @can('rate_head.delete')
                                            &nbsp;
                                            <a href="{{ route('rate_head.delete', $item->id) }}" class="btn btn-sm btn-danger delete" title="Delete" onclick="return confirm('Are you sure you want to delete this Rate Head?')">
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
