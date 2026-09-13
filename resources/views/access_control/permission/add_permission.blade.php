@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Add Permission</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{route('dashboard')}}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>Permission</span></li>
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
                            <form id="myForm" method="post" action="{{ route('permission.store') }}" enctype="multipart/form-data">
                                @csrf

                                <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> Add Permission</h5>

                                <div class="row">


                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="name" class="form-label">Permission Name</label>
                                            <input type="text" name="name" class="form-control"   >

                                        </div>
                                    </div>


                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="firstname" class="form-label">Group Name </label>
                                            <select name="group_name" class="form-select" id="example-select">
                                                <option selected disabled>Select Group</option>

                                                <option value="user"> User</option>
                                                <option value="import"> Import</option>
                                                <option value="committee_input_regular"> Committee Input Regular</option>
                                                <option value="committee_input_review"> Committee Input Review</option>
                                                <option value="committee_input_special"> Committee Input Special</option>
                                                <option value="report_regular"> Report Regular</option>
                                                <option value="report_review"> Report Review</option>
                                                <option value="report_special"> Report Special</option>
                                                <option value="statement"> Statement</option>
                                                <option value="teacher"> Teacher</option>
                                                <option value="employee"> Employee</option>
                                                <option value="session"> Session</option>
                                                <option value="contributor"> Contributor</option>
                                                <option value="permission"> Permission</option>
                                                <option value="rate_head"> Rate Head</option>
                                                <option value="role"> Role</option>
                                                <option value="role_permission"> Role Permission</option>
                                                <option value="role_assignment"> Role Assignment For User</option>
                                                <option value="menu"> Menu</option>
                                            </select>

                                        </div>
                                    </div>




                                </div> <!-- end row -->



                                <div class="text-end">
                                    <button type="submit" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i> Save</button>
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
