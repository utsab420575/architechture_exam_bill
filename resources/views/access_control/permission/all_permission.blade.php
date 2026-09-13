@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>All Permission</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{route('dashboard')}}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>Committee Input</span></li>
                    <li><span>Regular Session</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>


        <div class="row">
            <div class="col-md-12">
                <section class="card">
                    <div class="card-header d-flex justify-content-end">
                            <div>
                                    <a href="{{ route('permission.add') }}"
                                       class="btn btn-primary rounded-pill waves-effect waves-light">Add Permission </a>
                            </div>

                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped mb-0" id="datatable-default">
                            <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Permission Name</th>
                                <th>Group Name</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($permissions as $key=> $item)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->group_name }}</td>
                                    <td>
                                        <a href="{{ route('permission.edit',$item->id) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        &nbsp; &nbsp;
                                        <a href="{{ route('permission.delete',$item->id) }}"
                                           class="btn btn-sm btn-danger delete"
                                           id="delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </div>

        <!-- end row-->

    </section>
@endsection

@push('scripts')

@endpush
