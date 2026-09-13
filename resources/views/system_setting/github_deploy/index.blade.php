@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Github Deploy</h2>

            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{ route('dashboard') }}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>System Setting</span></li>
                    <li><span>Github Deploy</span></li>
                </ol>

                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <div class="row">
            <div class="col-md-12">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Repository Status</h2>
                    </header>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <div class="text-muted small">Current Branch</div>
                                <span class="badge bg-primary fs-6">{{ $currentBranch }}</span>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="text-muted small">Working Tree</div>
                                @if($isDirty)
                                    <span class="badge bg-warning text-dark">Uncommitted Changes</span>
                                @else
                                    <span class="badge bg-success">Clean</span>
                                @endif
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="text-muted small">Ahead / Behind Origin</div>
                                @if(!is_null($ahead) && !is_null($behind))
                                    <span class="badge bg-info text-dark">{{ $ahead }} ahead</span>
                                    <span class="badge bg-secondary">{{ $behind }} behind</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="text-muted small">Remote</div>
                                <span class="small">{{ $remoteUrl ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Pull Latest Code</h2>
                    </header>
                    <div class="card-body">
                        @can('github_deploy.pull')
                            @if($isDirty)
                                <div class="alert alert-warning py-2">
                                    There are uncommitted local changes. Pulling may fail or cause conflicts.
                                </div>
                            @endif
                            <form method="POST" action="{{ route('github_deploy.pull') }}"
                                  onsubmit="return confirm('This will checkout and pull the selected branch on the server. Continue?')">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="branch" class="form-label font-weight-bold">Select Branch</label>
                                    <select name="branch" id="branch" class="form-control" required>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch }}" {{ $branch === $currentBranch ? 'selected' : '' }}>
                                                {{ $branch }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-cloud-arrow-down me-1"></i> Pull
                                </button>
                            </form>
                        @else
                            <p class="text-muted mb-0">You do not have permission to pull code on this server.</p>
                        @endcan
                    </div>
                </section>

                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Last Pull Result</h2>
                    </header>
                    <div class="card-body">
                        @if($lastPullLog)
                            <pre class="bg-dark text-light p-3 rounded" style="max-height: 320px; overflow:auto; white-space: pre-wrap;">{{ $lastPullLog }}</pre>
                        @else
                            <p class="text-muted mb-0">No pull has been run from this page yet.</p>
                        @endif
                    </div>
                </section>
            </div>

            <div class="col-lg-7">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Recent Commits</h2>
                    </header>
                    <div class="card-body">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                            <tr>
                                <th>Hash</th>
                                <th>Author</th>
                                <th>When</th>
                                <th>Message</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($commits as $commit)
                                <tr>
                                    <td><span class="badge bg-dark">{{ $commit['hash'] }}</span></td>
                                    <td>{{ $commit['author'] }}</td>
                                    <td>{{ $commit['date'] }}</td>
                                    <td>{{ $commit['message'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No commit history found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection
