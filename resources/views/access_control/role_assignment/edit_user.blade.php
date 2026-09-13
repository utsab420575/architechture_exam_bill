@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Role Assign To User</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{route('dashboard')}}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>Role</span></li>
                    <li><span>Assign</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>


        <div class="row">


            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">


                        <!-- end timeline content-->

                        <form id="myForm" method="post" action="{{ route('role.assignments.update') }}"
                              enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" name="id" value="{{ $user->id }}">


                            <div class="form-group row pb-3">
                                <label class="col-md-2 control-label text-lg-end pt-2">Assign Roles</label>
                                <div class="col-md-10">
                                    <div class="input-group input-group-select-append">
                                        <span class="input-group-text">
                                            <i class="fas fa-th-list"></i> {{-- icon for roles --}}
                                        </span>
                                        <select name="roles[]" class="form-control" multiple data-plugin-multiselect
                                                data-plugin-options='{ "maxHeight": 200 }'>
                                            @foreach($roles as $role)
                                                <option
                                                    value="{{ $role->id }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="text-end">
                                <button type="submit" class="btn btn-success waves-effect waves-light mt-2"><i
                                        class="mdi mdi-content-save"></i> Save
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
