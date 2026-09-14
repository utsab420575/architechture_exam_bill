@push('styles')
    <style>
        .card-list-of-course-profile {
            background-color: white;
            transition: background-color 0.6s ease-in-out;
        }
        .card-list-of-course-profile.fade-highlight { background-color: #28a745; }
        .card-list-of-course-profile.fade-out { background-color: white; }

        select.is-invalid, input.is-invalid { border-color: red; }
    </style>
@endpush

<form id="form-list-of-course-profile" action="{{ route('committee.input.course.profile.store') }}" method="POST">
    @csrf
    <input type="hidden" name="sid" value="{{ $sid }}">

    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center">
                    <h2 class="card-title">
                        <span class="step-badge">17</span>
                        Course Profile (@ {{ $course_profile_per_course_rate ?? 6000 }}/- per course)
                    </h2>
                </header>

                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="course_profile_rate">Per course profile rate</label>
                                <input type="number"
                                       name="course_profile_rate"
                                       id="course_profile_rate"
                                       value="{{ $course_profile_per_course_rate ?? 6000 }}"
                                       step="any"
                                       class="form-control"
                                       placeholder="Enter per course rate"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4"></div>
                        <div class="col-md-4 mb-4"></div>
                    </div>

                    {{-- Per-course loop (all theory and sessional courses) --}}
                    <div class="row">
                        <div class="col-md-12">
                            @php
                                $coursesList = ($session_info->year != 6 && $session_info->semester != 3)
                                    ? ($all_theory_sessional_courses_with_student_count->courses ?? $all_course_with_teacher->courses ?? [])
                                    : ($all_course_with_teacher->courses ?? []);
                            @endphp

                            @if(!empty($coursesList))
                                @foreach($coursesList as $courseData)
                                    @php
                                        $single_course = $courseData->courseObject;
                                        $course_code   = $single_course->courseno;

                                        $savedForCourseProfile = $savedRateAssignCourseProfile[$course_code] ?? collect();
                                    @endphp

                                    {{-- Hidden course-level metadata --}}
                                    <input type="hidden" name="courseno[{{ $single_course->id }}]" value="{{ $single_course->courseno }}">
                                    <input type="hidden" name="coursetitle[{{ $single_course->id }}]" value="{{ $single_course->coursetitle }}">

                                    <section class="card card-featured card-featured-secondary">
                                        <header class="card-header">
                                            <h2 class="card-title">
                                                Course: {{ $single_course->courseno }} - {{ $single_course->coursetitle }}
                                            </h2>
                                        </header>

                                        <div class="card-body card-list-of-course-profile">
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <label for="course_profile_teacher_{{ $single_course->id }}_{{ $loop->index }}">
                                                        Select Teachers (Course Profile)
                                                    </label>
                                                    <select name="course_profile_teacher_ids[{{ $single_course->id }}][]"
                                                            id="course_profile_teacher_{{ $single_course->id }}_{{ $loop->index }}"
                                                            class="form-control populate"
                                                            data-plugin-selectTwo
                                                            multiple
                                                            >
                                                        <option value="" disabled>-- Select Teacher --</option>
                                                        @foreach($groupedTeachers as $deptFullName => $deptTeachers)
                                                            <optgroup label="{{ $deptFullName }}">
                                                                @foreach($deptTeachers as $teacher)
                                                                    <option value="{{ $teacher->id }}"
                                                                        {{ $savedForCourseProfile->pluck('teacher_id')->contains($teacher->id) ? 'selected' : '' }}>
                                                                        {{ $teacher->user->name }} - {{ $teacher->department->shortname }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                @endforeach
                            @endif

                            <div class="text-end mt-3">
                                <button id="submit-list-of-course-profile" type="submit" class="btn btn-primary">
                                    Submit Course Profile Committee
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('form-list-of-course-profile');

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
                    if (!result.isConfirmed) return;

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
                                return response.json().then(err => {
                                    if (err && err.errors) {
                                        Object.keys(err.errors).forEach(key => {
                                            const field = form.querySelector(`[name="${key}"]`);
                                            if (field) field.classList.add('is-invalid');
                                        });
                                    }
                                    throw new Error(err.message || 'Unknown error occurred.');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message || 'Saved successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });

                            // Update button state
                            const submitBtn = document.getElementById('submit-list-of-course-profile');
                            submitBtn.textContent = 'Update Course Profile Committee';
                            submitBtn.classList.remove('btn-primary');
                            submitBtn.classList.add('btn-warning');

                            // Green fade animation on all course cards
                            const cards = document.querySelectorAll('.card-list-of-course-profile');
                            cards.forEach(card => {
                                card.classList.add('fade-highlight');
                                setTimeout(() => card.classList.add('fade-out'), 1000);
                                setTimeout(() => card.classList.remove('fade-highlight','fade-out'), 1900);
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error!',
                                text: error.message || 'Something went wrong. Please try again.',
                                icon: 'error'
                            });
                        });
                });
            });
        });
    </script>
@endpush
