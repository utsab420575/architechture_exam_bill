@extends('layouts.app')
@section('content')
    {{--this is for to make workable image choose box--}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>All Teacher</h2>

            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{route('dashboard')}}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>

                    <li><span>Pages</span></li>

                    <li><span>All Teacher</span></li>

                </ol>

                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>
        <div class="row">
            <div class="col-md-12">
                <section class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-striped mb-0" id="datatable-default">
                            <thead>
                            <tr>
                                <th>SL NO.</th>
                                <th>Photo</th>
                                <th>Teacher Name</th>
                                <th>Email</th>
                                <th>Phone</th>

                                <th>Department</th>
                                <th>Designation</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($teachers as $key=>$teacher)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    @php
                                        $photoFile = $teacher->user->photo;
                                        $photoPath = $photoFile && file_exists(public_path($photoFile))
                                            ? asset($photoFile)
                                            : asset('upload/no_image.jpg');
                                    @endphp
                                    <td>
                                        <img src="{{ $photoPath }}" style="width:50px; height:40px;">
                                    </td>
                                    <td>{{$teacher->user->name}}</td>
                                    <td>{{$teacher->user->email}}</td>
                                    <td>{{$teacher->user->phone}}</td>
                                    <td>{{$teacher->department->shortname}}</td>
                                    <td>{{$teacher->designation->designation}}</td>
                                    <td class="text-center">
                                        <a href="{{ route('teacher.edit', $teacher->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        &nbsp;&nbsp;
                                        <a href="{{ route('teacher.delete', $teacher->id) }}" class="btn btn-sm btn-danger delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </div>
    </section>
@endsection

<script>

</script>
