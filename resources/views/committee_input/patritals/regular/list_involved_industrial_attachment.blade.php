@push('styles')
    <style>
        .card-list-of-involved-industrial-attachment {
            background-color: white;
            transition: background-color 0.6s ease-in-out;
        }
        .card-list-of-involved-industrial-attachment.fade-highlight { background-color: #28a745; }
        .card-list-of-involved-industrial-attachment.fade-out { background-color: white; }

        select.is-invalid, input.is-invalid { border-color: red; }
    </style>
@endpush

<form id="form-list-of-involved-industrial-attachment" action="{{ route('committee.input.involved.industrial.attachment.store') }}" method="POST">
    @csrf
    <input type="hidden" name="sid" value="{{ $sid }}">

    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center">
                    <h2 class="card-title">
                        <span class="step-badge">7.g</span>
                        List of teachers involved Industrial attachment/training (@ {{ $involved_industrial_attachment_per_student_rate ?? 750 }}/- per student)
                    </h2>
                </header>

                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="attachment_rate">Per Student Industrial attachment Rate</label>
                                <input type="number"
                                       name="attachment_rate"
                                       id="attachment_rate"
                                       value="{{ $involved_industrial_attachment_per_student_rate ?? 750 }}"
                                       step="any"
                                       class="form-control"
                                       placeholder="Enter per student rate"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4"></div>
                        <div class="col-md-4 mb-4"></div>
                    </div>

                    {{-- Per-course loop (same structure as 7.f) --}}
                    <div class="row">
                        <div class="col-md-12">
                            @if(isset($all_sessional_course_with_teacher->courses))
                                @foreach($all_sessional_course_with_teacher->courses as $courseData)
                                    @php
                                        $single_course = $courseData->courseObject;
                                        $course_code   = $single_course->courseno;

                                        $savedForInvolvedIndustrialAttachment = $savedRateAssignInvolvedIndustrialAttachment[$course_code] ?? collect();
                                    @endphp

                                    {{-- Hidden course-level metadata --}}
                                    <input type="hidden" name="courseno[{{ $single_course->id }}]" value="{{ $single_course->courseno }}">
                                    <input type="hidden" name="coursetitle[{{ $single_course->id }}]" value="{{ $single_course->coursetitle }}">
                                    <input type="hidden" name="registered_students_count[{{ $single_course->id }}]" value="{{ $courseData->registered_students_count }}">

                                    <section class="card card-featured card-featured-secondary">
                                        <header class="card-header">
                                            <h2 class="card-title">
                                                Course: {{ $single_course->courseno }} - {{ $single_course->coursetitle }}
                                            </h2>
                                        </header>

                                        <div class="card-body card-list-of-involved-industrial-attachment">
                                            <div class="row mb-3">
                                                <div class="col-md-9">
                                                    <label for="involved_industrial_attachment_teacher_{{ $single_course->id }}_{{ $loop->index }}">
                                                        Select Teachers (Involved Industrial attachment/training)
                                                    </label>
                                                    <select name="involved_industrial_attachment_teacher_ids[{{ $single_course->id }}][]"
                                                            id="involved_industrial_attachment_teacher_{{ $single_course->id }}_{{ $loop->index }}"
                                                            class="form-control populate"
                                                            data-plugin-selectTwo
                                                            multiple
                                                            >
                                                        <option value="" disabled>-- Select Teacher --</option>
                                                        @foreach($groupedTeachers as $deptFullName => $deptTeachers)
                                                            <optgroup label="{{ $deptFullName }}">
                                                                @foreach($deptTeachers as $teacher)
                                                                    <option value="{{ $teacher->id }}"
                                                                        {{ $savedForInvolvedIndustrialAttachment->pluck('teacher_id')->contains($teacher->id) ? 'selected' : '' }}>
                                                                        {{ $teacher->user->name }} - {{ $teacher->department->shortname }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-3">
                                                    @php
                                                        $noOfItems = $savedForInvolvedIndustrialAttachment->isNotEmpty()
                                                            ? $savedForInvolvedIndustrialAttachment->first()->total_students
                                                            : $courseData->registered_students_count;
                                                    @endphp
                                                    <label for="involved_industrial_attachment_no_of_students_{{ $single_course->id }}">
                                                        No of Students
                                                    </label>
                                                    <input type="number"
                                                           name="involved_industrial_attachment_no_of_students[{{ $single_course->id }}]"
                                                           id="involved_industrial_attachment_no_of_students_{{ $single_course->id }}"
                                                           class="form-control"
                                                           min="0"
                                                           step="any"
                                                           value="{{ old('involved_industrial_attachment_no_of_students.' . $single_course->id, $noOfItems) }}"
                                                           >
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                @endforeach
                            @endif

                            <div class="text-end mt-3">
                                <button id="submit-list-of-involved-industrial-attachment" type="submit" class="btn btn-primary">
                                    Submit Industrial Attachment Committee
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
            const form = document.getElementById('form-list-of-involved-industrial-attachment');

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
                            const submitBtn = document.getElementById('submit-list-of-involved-industrial-attachment');
                            submitBtn.textContent = 'Update Industrial Attachment Committee';
                            submitBtn.classList.remove('btn-primary');
                            submitBtn.classList.add('btn-warning');

                            // Green fade animation on all course cards
                            const cards = document.querySelectorAll('.card-list-of-involved-industrial-attachment');
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
