@extends('layouts.app')
@section('content')
    {{--this is for to make workable image choose box--}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Teacher Add</h2>

            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{route('dashboard')}}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>

                    <li><span>Teacher</span></li>

                    <li><span>Add Teacher</span></li>

                </ol>

                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <!-- start: page -->

        <div class="row">


            <div class="col-lg-8 col-xl-8">
                <div class="card">
                    <div class="card-body">

                        <div id="edit" class="tab-pane">
                            <form method="POST" action="{{route('employee.update')}}" class="p-3" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" value="{{$employee->id}}" name="id">
                                <h4 class="mb-3 font-weight-semibold text-dark">Teacher Information</h4>
                                <div class="row row mb-4">
                                    <div class="form-group col">
                                        <label for="inputAddress">Name</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               placeholder="Enter Name" value="{{ $employee->user->name }}" required>
                                        @error('name')
                                        <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label for="phone">Enter Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                               placeholder="Enter your phone" value="{{ $employee->user->phone  }}" required>
                                        @error('phone')
                                        <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label for="email">Enter Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               placeholder="Enter your email" value="{{ $employee->user->email  }}" required>
                                        @error('email')
                                        <span class="text-danger"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label for="address">Enter Address</label>
                                        <textarea class="form-control" rows="3" id="address" name="address">{{ $employee->preaddress }}</textarea>
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
                                                <option value="{{$designation->id}}" {{ $employee->designation_id == $designation->id ? 'selected' : '' }}>
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
                                                <option value="{{$department->id}}" {{ $employee->department_id == $department->id ? 'selected' : '' }}>
                                                    {{$department->fullname}}
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
                                        <img id="showPhoto" src="{{ (!empty($employee->user->photo)) ? url($employee->user->photo) : url('upload/no_image.jpg') }}"
                                             class="rounded-circle img-thumbnail"
                                             style="width: 150px; height: 150px; object-fit: cover;"
                                             alt="profile-image">
                                    </div>
                                </div> <!-- end col -->


                                <div class="row">
                                    <div class="col-md-12 text-end mt-3">
                                        <button class="btn btn-primary modal-confirm">Update Teacher</button>
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

    @push('scripts')
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

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('form-list-of-advisor-student');

                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to save the committee data?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, save it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const formData = new FormData(form);

                            fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: formData
                            })
                                .then(response => {
                                    if (!response.ok) {
                                        // Return the error JSON and throw it
                                        return response.json().then(err => {
                                            throw new Error(err.message || 'Unknown error occurred.');
                                        });
                                    }
                                    return response.json(); // if response is OK
                                })
                                .then(data => {
                                    console.log("Server response:", data); // Debug log
                                    Swal.fire({
                                        title: 'Success!',
                                        text: data.message,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    });

                                    const submitBtn = document.getElementById('submit-list-of-advisor-student');
                                    submitBtn.textContent = 'Already Saved';             // ✅ Change text
                                    submitBtn.disabled = true;                           // ✅ Disable button
                                    submitBtn.classList.remove('btn-primary');           // ✅ Remove old style
                                    submitBtn.classList.add('btn-success');              // ✅ Add success style

                                    const cards = document.querySelectorAll('.card-list-of-advisor-student');

                                    cards.forEach(card => {
                                        card.classList.add('fade-highlight');

                                        setTimeout(() => {
                                            card.classList.add('fade-out');
                                        }, 1000);

                                        setTimeout(() => {
                                            card.classList.remove('fade-highlight', 'fade-out');
                                        }, 1900);
                                    });


                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.fire({
                                        title: 'Error!',
                                        text: error.message||'Something went wrong. Please try again.',
                                        icon: 'error'
                                    });
                                });
                        }
                    });
                });
            });
        </script>
    @endpush


@endsection
