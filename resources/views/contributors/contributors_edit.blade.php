@extends('layouts.app')
@section('content')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Contributor Edit</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li><span>Contributors</span></li>
                    <li><span>Edit</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <div class="row">
            <div class="col-lg-8 col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <div id="edit" class="tab-pane">
                            <form method="POST" action="{{ route('contributors.update') }}" class="p-3" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="{{ $contributor->id }}">

                                <h4 class="mb-3 font-weight-semibold text-dark">Contributor Information</h4>

                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="name"
                                               value="{{ old('name', $contributor->name) }}" required>
                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label>Designation</label>
                                        <input type="text" class="form-control" name="designation"
                                               value="{{ old('designation', $contributor->designation) }}" required>
                                        @error('designation') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label>Profile Link</label>
                                        <input type="text" class="form-control" name="profile"
                                               value="{{ old('profile', $contributor->profile) }}">
                                        @error('profile') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label>Speech / Short Note</label>
                                        <textarea class="form-control" rows="4" name="speech">{{ old('speech', $contributor->speech) }}</textarea>
                                        @error('speech') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>


                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label>Order</label>
                                        <input type="number" class="form-control" name="order"
                                               value="{{ old('order', $contributor->order) }}" required>
                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>


                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label>Sequence</label>
                                        <input type="number" class="form-control" name="sequence"
                                               value="{{ old('sequence', $contributor->sequence) }}" required>
                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>


                                <div class="row mb-4">
                                    <div class="form-group col">
                                        <label for="photo">Photo</label>
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                        </div>
                                    </div>
                                </div>


                                @php
                                    $photoFile = $contributor->photo;
                                    $photoPath = ($photoFile && file_exists(public_path($photoFile)))
                                        ? asset($photoFile)
                                        : asset('upload/no_image.jpg');
                                @endphp
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <img id="showPhoto" src="{{ $photoPath }}"
                                             class="rounded-circle img-thumbnail"
                                             style="width: 150px; height: 150px; object-fit: cover;" alt="preview">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 text-end mt-3">
                                        <button class="btn btn-primary modal-confirm">Update Contributor</button>
                                    </div>
                                </div>
                            </form>

                            {{-- preview --}}
                            <script>
                                $(document).ready(function(){
                                    $('#photo').change(function(e){
                                        const reader = new FileReader();
                                        reader.onload = function(e){
                                            $('#showPhoto').attr('src', e.target.result);
                                        }
                                        reader.readAsDataURL(e.target.files[0]);
                                    });
                                });
                            </script>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
