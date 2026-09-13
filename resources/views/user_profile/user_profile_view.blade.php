@extends('layouts.app')
@section('content')
    {{--this is for to make workable image choose box--}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>User Profile</h2>

            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{route('dashboard')}}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>

                    <li><span>Pages</span></li>

                    <li><span>User Profile</span></li>

                </ol>

                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <!-- start: page -->

        <div class="row">
            <div class="col-lg-4 col-xl-4 mb-4 mb-xl-0">

                <section class="card">
                    <div class="card-body">
                        <div class="thumb-info mb-3">
                            <img
                                src="{{!empty($user->photo)?url($user->photo) :url('upload/no_image.jpg')}}"
                                class="rounded img-fluid" alt="No Image">
                            {{--<img src="{{asset('backend/assets/img/!logged-user.jpg')}}" class="rounded img-fluid" alt="John Doe">--}}
                            <div class="thumb-info-title">
                                <span class="thumb-info-inner">{{$user->name}}</span>
                                @if($user->roles->isNotEmpty())
                                    <span class="thumb-info-type">{{ $user->roles->first()->name }}</span>
                                @else
                                    <span class="thumb-info-type">No Role</span>
                                @endif
                            </div>
                        </div>

                        <div class="widget-toggle-expand mb-3">
                            <div class="widget-header">
                                <h5 class="mb-2 font-weight-semibold text-dark">Profile Data</h5>
                                <div class="widget-toggle">+</div>
                            </div>
                            <div class="widget-content-expanded">
                                <ul class="simple-todo-list mt-3">
                                    <li class="completed">Name : {{$user->name}}</li>
                                    <li class="completed">Phone : {{$user->phone}}</li>
                                    <li class="completed">Email : {{$user->email}}</li>
                                </ul>
                            </div>
                        </div>

                        <hr class="dotted short">


                        <hr class="dotted short">

                    </div>
                </section>

            </div>
            <div class="col-lg-8 col-xl-8">
                <div class="card">
                    <div class="card-body">

                        <div id="edit" class="tab-pane">
                            <form method="POST" action="{{route('user.profile.store')}}" class="p-3" enctype="multipart/form-data">
                                @csrf
                                <h4 class="mb-3 font-weight-semibold text-dark">Personal Information</h4>
                                <div class="row row mb-4">
                                    <div class="form-group col">
                                        <label for="inputAddress">Name</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="{{$user->name}}"
                                               placeholder="Enter Name" required>
                                        @error('name')
                                            <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label for="phone">Enter Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                               value="{{$user->phone}}"
                                               placeholder="Enter your phone" required>
                                        @error('phone')
                                            <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label for="phone">Enter Email</label>
                                        <input type="text" class="form-control" id="email" name="email"
                                               value="{{$user->email}}"
                                               placeholder="Enter your email" required>
                                        @error('email')
                                            <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>





                                {{--<div class="row mb-4">
                                    <div class="form-group col">
                                        <label for="designation">Enter Designation</label>
                                        <select class="form-control" name="designation" id="designation" required>
                                            <option value="" selected disabled>Select Designation</option>
                                            @foreach($designations as $designation)
                                                <option value="{{$designation->id}}"  {{ isset($user->teacher) && $user->teacher->designation_id == $designation->id ? 'selected' : '' }}>
                                                    {{$designation->designation}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('designation')
                                        <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label for="department">Enter Department</label>
                                        <select class="form-control" name="department" id="department" required>
                                            <option value="" selected disabled>Select Department</option>
                                            @foreach($departments as $department)
                                                <option value="{{$department->id}}"  {{ isset($user->teacher) && $user->teacher->department_id == $department->id ? 'selected' : '' }}>
                                                    {{$department->fullname}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('department')
                                        <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>--}}

                                @php
                                    $isTeacher = $user->hasRole('Teacher');
                                    $model = $isTeacher ? $user->teacher : $user->employee;
                                @endphp

                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label for="address">Enter Address</label>
                                        <textarea class="form-control" rows="3" id="address" name="address">{{ $model->preaddress ?? '' }}</textarea>
                                        @error('address')
                                        <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label for="designation">Enter Designation</label>
                                        <select class="form-control" name="designation" id="designation" required>
                                            <option value="" selected disabled>Select Designation</option>
                                            @foreach($designations as $designation)
                                                <option value="{{ $designation->id }}"
                                                    {{ $model && $model->designation_id == $designation->id ? 'selected' : '' }}>
                                                    {{ $designation->designation }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('designation')
                                        <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label for="department">Enter Department</label>
                                        <select class="form-control" name="department" id="department" required>
                                            <option value="" selected disabled>Select Department</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}"
                                                    {{ $model && $model->department_id == $department->id ? 'selected' : '' }}>
                                                    {{ $department->fullname }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('department')
                                        <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>





                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label for="photo">Photo</label>
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="photo" name="photo">
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="showImage" class="form-label"> </label>
                                        <img id="showPhoto" src="{{ (!empty($user->photo)) ? url($user->photo) : url('upload/no_image.jpg') }}"
                                             class="rounded-circle img-thumbnail"
                                             style="width: 150px; height: 150px; object-fit: cover;"
                                             alt="profile-image">
                                    </div>
                                </div> <!-- end col -->


                                <div class="row">
                                    <div class="col-md-12 text-end mt-3">
                                        <button class="btn btn-primary modal-confirm">Update Profile</button>
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


    {{--for showing image when select choose image--}}
    <script type="text/javascript">

        $(document).ready(function(){
            $('#photo').change(function(e){
                var reader = new FileReader();
                reader.onload =  function(e){
                    $('#showPhoto').attr('src',e.target.result);
                }
                reader.readAsDataURL(e.target.files['0']);
            });
        });

    </script>
@endsection
