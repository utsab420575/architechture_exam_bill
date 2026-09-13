@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Edit Rate Head</h2>

            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{ route('dashboard') }}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>System Setting</span></li>
                    <li><span>Edit Rate Head</span></li>
                </ol>

                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <div class="row">
            <div class="col-lg-10 col-xl-8">
                <div class="card">
                    <header class="card-header">
                        <h2 class="card-title">Edit Rate Head Form</h2>
                    </header>
                    <div class="card-body">
                        <form method="POST" action="{{ route('rate_head.update') }}" class="p-3">
                            @csrf
                            <input type="hidden" name="id" value="{{ $rate_head->id }}">

                            <div class="row mb-3">
                                <div class="form-group col-md-8">
                                    <label for="head" class="form-label font-weight-bold">Head / Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="head" name="head"
                                           placeholder="e.g. Moderation Committee" value="{{ old('head', $rate_head->head) }}" required>
                                    @error('head')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="order_no" class="form-label font-weight-bold">Order No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="order_no" name="order_no"
                                           placeholder="e.g. 1, 2, 8.a" value="{{ old('order_no', $rate_head->order_no) }}" required>
                                    @error('order_no')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="form-group col-md-6">
                                    <label for="sub_head" class="form-label font-weight-bold">Sub Head</label>
                                    <input type="text" class="form-control" id="sub_head" name="sub_head"
                                           placeholder="Enter sub head (optional)" value="{{ old('sub_head', $rate_head->sub_head) }}">
                                    @error('sub_head')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="dist_type" class="form-label font-weight-bold">Distribution Type <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="dist_type" name="dist_type"
                                           placeholder="e.g. individual, equal, shared" value="{{ old('dist_type', $rate_head->dist_type) }}" required>
                                    @error('dist_type')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="form-group col-md-12">
                                    <label for="marge_with" class="form-label font-weight-bold">Merge With (Parent Rate Head)</label>
                                    <select class="form-control" name="marge_with" id="marge_with">
                                        <option value="">-- None --</option>
                                        @foreach($parent_rate_heads as $p_head)
                                            <option value="{{ $p_head->id }}" {{ old('marge_with', $rate_head->marge_with) == $p_head->id ? 'selected' : '' }}>
                                                {{ $p_head->head }} (Order: {{ $p_head->order_no }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('marge_with')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <hr>
                            <h5 class="font-weight-semibold mb-3">Configuration Flags</h5>

                            <div class="row mb-3">
                                <div class="col-md-6 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable_min" name="enable_min" value="1" {{ old('enable_min', $rate_head->enable_min) ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-semibold" for="enable_min">Enable Minimum Rate Limit</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable_max" name="enable_max" value="1" {{ old('enable_max', $rate_head->enable_max) ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-semibold" for="enable_max">Enable Maximum Rate Limit</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_course" name="is_course" value="1" {{ old('is_course', $rate_head->is_course) ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-semibold" for="is_course">Is Course Dependent?</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_student_count" name="is_student_count" value="1" {{ old('is_student_count', $rate_head->is_student_count) ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-semibold" for="is_student_count">Is Student Count Dependent?</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status', $rate_head->status) ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-semibold" for="status">Active Status</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa-solid fa-save me-1"></i> Update Rate Head
                                    </button>
                                    <a href="{{ route('rate_head.all') }}" class="btn btn-secondary ms-2">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
