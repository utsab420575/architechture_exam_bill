@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Update Password</h2>

            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{route('dashboard')}}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>

                    <li><span>Profile</span></li>

                    <li class="pe-3"><span>Update Password</span></li>

                </ol>

                {{--<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>--}}
            </div>
        </header>

        <!-- start: page -->

        <div class="row">


            <div class="col-lg-8 col-xl-8">
                <div class="card">
                    <div class="card-body">

                        @php
                            $user=auth()->user();
                        @endphp
                        <div id="edit" class="tab-pane">
                            @if(session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif


                            <form method="POST" action="{{route('user.password.update')}}" class="p-3">
                                @csrf
                                <div class="row row mb-4">
                                    <div class="form-group col">
                                        <label for="old_password">Old Password</label>
                                        <input type="password" class="form-control" id="old_password" name="old_password"
                                               placeholder="Enter Old Password" required>
                                        @error('old_password')
                                        <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row row mb-4">
                                    <div class="form-group col">
                                        <label for="new_password">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password"
                                               placeholder="Enter New Password" required>
                                        @error('new_password')
                                        <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row row mb-4">
                                    <div class="form-group col">
                                        <label for="new_password_confirmation">Confirm Password</label>
                                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation"
                                               placeholder="Re-enter New Password" required>
                                        @error('new_password_confirmation')
                                        <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>



                                <div class="row">
                                    <div class="col-md-12 text-end mt-3">
                                        <button class="btn btn-primary modal-confirm">Update Password</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>


        </div>
        <!-- end: page -->
    </section>

@endsection
