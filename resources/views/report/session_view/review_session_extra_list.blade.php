@extends('layouts.app')

@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>All Sessions</h2>

            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="index.html">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>

                    <li><span>UI Elements</span></li>

                    <li><span>All Session</span></li>

                </ol>

                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <!-- start: page -->
        <div class="row">
            <div class="col">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Select Regular Session</h2>
                    </header>
                    <div class="card-body">
                        <form class="form-horizontal form-bordered" method="post" action="{{route('report.review.generate')}}">
                            @csrf
                            <div class="form-group row pb-1">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Select Session</label>
                                <div class="col-lg-6">
                                    @php use Illuminate\Support\Facades\Crypt; @endphp
                                    <select class="form-control mb-3" name="sid" required>
                                        <option selected disabled value="">-- Select Session --</option>
                                        @foreach($sessions as $session)
                                            {{--<option value="{{ Crypt::encryptString($session['id']) }}">--}}
                                            <option value="{{ $session->id}}">
                                                {{ $session->session }} - Year {{ $session->year }}, Semester {{ $session->semester }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="mb-1 justify-content-center btn btn-success btn-lg btn-block">Submit Session</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
        <!-- end: page -->
    </section>
@endsection
