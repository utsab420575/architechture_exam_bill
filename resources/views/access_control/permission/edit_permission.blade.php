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

        {{--content start--}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- end timeline content-->
                            <form method="post" action="{{ route('permission.update') }}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $permission->id }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="firstname" class="form-label">Permission Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $permission->name }}"  >

                                        </div>
                                    </div>


                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="firstname" class="form-label">Group Name</label>
                                            <select name="group_name" class="form-select" id="example-select">
                                                <option disabled {{ is_null($permission->group_name) ? 'selected' : '' }}>Select Group</option>

                                                <option value="user" {{ $permission->group_name == 'user' ? 'selected' : '' }}> User</option>
                                                <option value="import" {{ $permission->group_name == 'import' ? 'selected' : '' }}> Import</option>
                                                <option value="committee_input_regular" {{ $permission->group_name == 'committee_input_regular' ? 'selected' : '' }}> Committee Input Regular</option>
                                                <option value="committee_input_review" {{ $permission->group_name == 'committee_input_review' ? 'selected' : '' }}> Committee Input Review</option>
                                                <option value="report_regular" {{ $permission->group_name == 'report_regular' ? 'selected' : '' }}> Report Regular</option>
                                                <option value="report_review" {{ $permission->group_name == 'report_review' ? 'selected' : '' }}> Report Review</option>
                                                <option value="statement" {{ $permission->group_name == 'statement' ? 'selected' : '' }}> Statement</option>
                                                <option value="teacher" {{ $permission->group_name == 'teacher' ? 'selected' : '' }}> Teacher</option>
                                                <option value="employee" {{ $permission->group_name == 'employee' ? 'selected' : '' }}> Employee</option>
                                                <option value="session" {{ $permission->group_name == 'session' ? 'selected' : '' }}> Session</option>
                                                <option value="contributor" {{ $permission->group_name == 'contributor' ? 'selected' : '' }}> Contributor</option>
                                                <option value="permission" {{ $permission->group_name == 'permission' ? 'selected' : '' }}> Permission</option>
                                                <option value="role" {{ $permission->group_name == 'role' ? 'selected' : '' }}> Role</option>
                                                <option value="role_permission" {{ $permission->group_name == 'role_permission' ? 'selected' : '' }}> Role Permission</option>
                                                <option value="role_assignment" {{ $permission->group_name == 'role_assignment' ? 'selected' : '' }}> Role Assignment For User</option>
                                                <option value="menu" {{ $permission->group_name == 'menu' ? 'selected' : '' }}> Menu</option>
                                            </select>
                                        </div>
                                    </div>




                                </div> <!-- end row -->



                                <div class="text-end">
                                    <button type="submit" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i> Update</button>
                                </div>
                            </form>
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
