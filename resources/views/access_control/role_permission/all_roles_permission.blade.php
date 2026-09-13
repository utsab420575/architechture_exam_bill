@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Role In Permission</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{route('dashboard')}}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>Role in permission</span></li>
                    <li><span>All</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>


        {{--content start--}}
        <div class="row">
            <div class="col-md-12">
                <section class="card">
                    <div class="card-header d-flex justify-content-end">
                        <div>
                            <a href="{{ route('roles.permissions.add') }}"
                               class="btn btn-primary rounded-pill waves-effect waves-light">Add Role in
                                Permission </a>
                        </div>

                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped mb-0" id="datatable-default">
                            <thead>
                                <tr>
                                    <th>Sl</th>
                                    <th>Roles Name</th>
                                    <th>Permission Name</th>
                                    <th width="18%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $index => $role)
                                    <tr>
                                        <td>{{ $index+1 }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>
                                            @foreach($role->permissions as $perm)
                                                <span class="badge rounded-pill bg-danger"> {{ $perm->name }} </span>
                                            @endforeach

                                        </td>
                                        <td width="18%" class="text-start">
                                           {{-- @if(Auth::user()->can('role.permission.edit'))--}}
                                                <a href="{{ route('role.permission.edit',$role->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                            &nbsp;&nbsp;
                                           {{-- @endif--}}
                                            {{--@if(Auth::user()->can('role.permission.delete'))--}}
                                                <a href="{{ route('role.permission.delete',$role->id) }}"
                                                   class="btn btn-sm btn-danger delete"
                                                   id="delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                           {{-- @endif--}}
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
