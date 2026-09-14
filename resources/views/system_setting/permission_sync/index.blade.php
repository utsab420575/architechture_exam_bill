@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Permission Sync</h2>

            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{ route('dashboard') }}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>System Setting</span></li>
                    <li><span>Permission Sync</span></li>
                </ol>

                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <div class="row">
            <div class="col-md-12">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Route / Permission Summary</h2>
                    </header>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="text-muted small">Named Routes in web.php</div>
                                <span class="badge bg-primary fs-6">{{ $totalRoutes }}</span>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="text-muted small">Already Registered as Permissions</div>
                                <span class="badge bg-success fs-6">{{ $existingCount }}</span>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="text-muted small">Missing Permissions</div>
                                <span class="badge {{ $missingRoutes->count() ? 'bg-warning text-dark' : 'bg-success' }} fs-6">{{ $missingRoutes->count() }}</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <section class="card">
                    <header class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="card-title">Routes Missing From the Permissions Table</h2>
                    </header>
                    <div class="card-body">
                        @if($missingRoutes->isEmpty())
                            <p class="text-muted mb-0">Every named route in <code>routes/web.php</code> already has a matching permission. Nothing to sync.</p>
                        @else
                            @if(Auth::user()->can('permission_sync.sync') || Auth::user()->hasRole('SuperAdmin'))
                                <form method="POST" action="{{ route('permission_sync.sync') }}">
                                    @csrf
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped align-middle mb-3">
                                            <thead>
                                            <tr>
                                                <th style="width: 40px;">
                                                    <input type="checkbox" id="select-all" title="Select all">
                                                </th>
                                                <th>Route Name</th>
                                                <th style="width: 260px;">Group Name</th>
                                                <th style="width: 90px;" class="text-center">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($missingRoutes as $i => $route)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="row-select" name="rows[{{ $i }}][selected]" value="1">
                                                    </td>
                                                    <td>
                                                        <code>{{ $route['name'] }}</code>
                                                        <input type="hidden" name="rows[{{ $i }}][name]" value="{{ $route['name'] }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                               name="rows[{{ $i }}][group_name]"
                                                               value="{{ $route['suggested_group'] }}">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="submit" name="quick_save_name" value="{{ $route['name'] }}"
                                                                class="btn btn-sm btn-primary" title="Save just this permission">
                                                            <i class="fa-solid fa-floppy-disk"></i> Save
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" name="bulk_save" value="1" class="btn btn-success">
                                        <i class="fa-solid fa-check-double me-1"></i> Save Selected
                                    </button>
                                </form>
                            @else
                                <p class="text-muted mb-0">You do not have permission to add new permissions.</p>
                            @endif
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.getElementById('select-all')?.addEventListener('change', function () {
            document.querySelectorAll('.row-select').forEach(function (checkbox) {
                checkbox.checked = this.checked;
            }, this);
        });
    </script>
@endpush
