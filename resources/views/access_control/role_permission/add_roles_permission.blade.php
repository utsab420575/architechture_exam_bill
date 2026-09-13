@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Add Role In Permission</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{route('dashboard')}}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>Role In Permission</span></li>
                    <li><span>Add</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>


        <div class="row">


            <div class="col-lg-8 col-xl-12">
                <div class="card">
                    <div class="card-body">


                        <!-- end timeline content-->
                        <form id="myForm" method="post" action="{{  route('role.permission.store') }}"
                              enctype="multipart/form-data">
                            @csrf

                            <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> Add Role
                                In Permission</h5>


                            {{-- this row for upper role showing--}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="firstname" class="form-label">All Roles </label>
                                        <select name="role_id" class="form-select" id="example-select" required>
                                            <option selected disabled value="">Select Roles</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}"> {{ $role->name }}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>

                                <div class="checkbox-custom checkbox-success ms-1">
                                    <input class="form-check-input" type="checkbox" value="" id="select_all">
                                    <label class="form-check-label" for="select_all">Select All</label>
                                </div>
                            </div>
                            {{--end upper row--}}


                            <hr>
                            @foreach($permissions as $groupName => $groupPermissions)
                                {{--one group will be in one row--}}
                                <div class="row">
                                    {{--Left Side Checkbox--}}
                                    {{--<div class="form-check-primary"> this make checkbox color magenta--}}
                                    {{--<div class="form-check-success"> this make checkbox color green--}}
                                    <div class="col-3">
                                        <div class="checkbox-custom checkbox-primary">
                                            <input class="form-check-input  group-checkbox" type="checkbox" value=""
                                                   id="group_{{ $loop->index }}">
                                            <label class="form-check-label"
                                                   for="group_{{ $loop->index }}">{{$groupName}}</label>
                                        </div>

                                    </div>

                                    {{--Right side checkbox--}}
                                    <div class="col-9">
                                        @foreach($groupPermissions as $permission)
                                            <div class="checkbox-custom checkbox-default">
                                                <input
                                                    class="form-check-input permission-checkbox group_{{ $loop->parent->index }}"
                                                    name="permission[]"
                                                    type="checkbox"
                                                    value="{{ $permission->id }}"
                                                    id="perm_{{ $permission->id }}">

                                                <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                    {{$permission->name}}
                                                </label>
                                            </div>

                                        @endforeach
                                    </div>

                                </div> <!-- end row -->
                                <br>
                            @endforeach


                            <div class="text-end">
                                <button type="submit" class="btn btn-success waves-effect waves-light mt-2"><i
                                        class="mdi mdi-content-save"></i> Save
                                </button>
                            </div>
                        </form>
                    </div> <!-- end card body-->
                </div> <!-- end card-->
            </div> <!-- end col-->

        </div> <!-- end row -->


    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Toggle all checkboxes when "Select All" is clicked
            $('#select_all').on('change', function () {
                var checked = $(this).is(':checked');
                $('.form-check-input').prop('checked', checked);
            });

            // Toggle group checkboxes' children
            $('.group-checkbox').each(function (index) {
                $(this).on('change', function () {
                    $('.group_' + index).prop('checked', $(this).is(':checked'));
                });
            });

            /*  // Optional: update group checkbox if all children are manually checked/unchecked
              $('.permission-checkbox').on('change', function () {
                  var groupIndex = this.className.match(/group_(\d+)/)[1];
                  var allChecked = $('.group_' + groupIndex).length === $('.group_' + groupIndex + ':checked').length;
                  $('#group_' + groupIndex).prop('checked', allChecked);
              });*/
        });
    </script>
@endpush
