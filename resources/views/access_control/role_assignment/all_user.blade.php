@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Regular Session All Form</h2>
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
            <div class="col-12">
                <div class="card">
                    <div class="card-body">


                        <table class="table table-bordered table-striped mb-0" id="datatable-default">
                            <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                            </thead>


                            <tbody>
                            @foreach($allUsers as $key=> $user)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td><img
                                            src="{{ (!empty($user->photo)) ? asset($user->photo) : url('upload/no_image.jpg') }}"
                                            style="width:50px; height: 40px;"></td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>
                                       {{-- @foreach($user->roles as $role)
                                            <span class="badge badge-pill bg-danger"> {{ $role->name }} </span>
                                        @endforeach--}}
                                        @foreach($user->roles ?? [] as $role)
                                            <span class="badge badge-pill bg-danger"> {{ $role->name }} </span>
                                        @endforeach
                                    </td>

                                    <td>
                                        {{--@if(Auth::user()->can('role.assignments.edit'))--}}
                                            <a href="{{ route('role.assignments.edit',$user->id) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                       {{-- @endif--}}
                                        {{--@if(Auth::user()->can('role.assignments.delete'))--}}
                                            <a href="{{ route('role.assignments.delete',$user->id) }}"
                                               class="btn btn-sm btn-danger delete"
                                               id="delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        {{--@endif--}}

                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div> <!-- end card body-->
                </div> <!-- end card -->
            </div><!-- end col-->
        </div>

    </section>
@endsection

@push('scripts')

@endpush
