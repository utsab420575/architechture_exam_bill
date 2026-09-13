@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>All Contributors</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li><span>Contributors</span></li>
                    <li><span>All</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <div class="row">
            <div class="col-md-12">
                <section class="card">
                    <div class="card-body">
                        <div class="mb-3 text-end">
                            <a href="{{ route('contributors.add') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Add Contributor
                            </a>
                        </div>

                        <table class="table table-bordered table-striped mb-0" id="datatable-default">
                            <thead>
                            <tr>
                                <th>SL NO.</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Profile</th>
                                <th>Order</th>
                                <th>Sequence</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($contributors as $key => $c)
                                @php
                                    $photoFile = $c->photo;
                                    $photoPath = ($photoFile && file_exists(public_path($photoFile)))
                                        ? asset($photoFile)
                                        : asset('upload/no_image.jpg');
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td><img src="{{ $photoPath }}" style="width:50px;height:40px;object-fit:cover;"></td>
                                    <td>{{ $c->name }}</td>
                                    <td>{{ $c->designation }}</td>
                                    <td>
                                        @if($c->profile)
                                            <a href="{{ $c->profile }}" target="_blank" rel="noopener">Open</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{$c->order}}</td>
                                    <td>{{$c->sequence}}</td>
                                    <td class="text-center">
                                        <a href="{{ route('contributors.edit', $c->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        &nbsp;&nbsp;
                                        <a href="{{ route('contributors.delete', $c->id) }}" class="btn btn-sm btn-danger delete">
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
