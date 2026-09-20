@extends('layouts.app')

@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Held On Entry — Special Sessions</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li><span>Held On Entry</span></li>
                    <li><span>Special Sessions</span></li>
                </ol>
            </div>
        </header>

        <div class="row">
            <div class="col">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Enter Exam Held On Dates — All Active Special Sessions</h2>
                    </header>
                    <div class="card-body">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>{{ session('success') }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form id="form-held-on" method="POST" action="{{ route('held.on.store') }}">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Session</th>
                                            <th>Year</th>
                                            <th>Semester</th>
                                            <th>Held On Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sessions as $index => $session)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $session->session }}</strong></td>
                                                <td>{{ $session->year }}</td>
                                                <td>{{ $session->semester }}</td>
                                                <td>
                                                    <input
                                                        type="date"
                                                        name="held_on[{{ $session->id }}]"
                                                        class="form-control"
                                                        value="{{ $session->held_on ? \Carbon\Carbon::parse($session->held_on)->format('Y-m-d') : '' }}"
                                                    >
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No active special sessions found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($sessions->isNotEmpty())
                                <div class="text-end mt-3">
                                    <button type="submit" id="btn-save-held-on" class="btn btn-primary btn-lg">
                                        <i class="fa-regular fa-floppy-disk"></i> Save All Dates
                                    </button>
                                </div>
                            @endif
                        </form>

                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-held-on');
        const btn  = document.getElementById('btn-save-held-on');

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Save Held On Dates?',
                text: 'Do you want to save the held on dates for all sessions?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
