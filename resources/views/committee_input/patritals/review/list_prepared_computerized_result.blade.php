@push('styles')
    <style>
        .card-list-of-prepared-computerized-result {
            background-color: white; /* starting point */
            transition: background-color 0.6s ease-in-out;
        }

        .card-list-of-prepared-computerized-result.fade-highlight {
            background-color: #28a745; /* strong green */
        }

        .card-list-of-prepared-computerized-result.fade-out {
            background-color: white;
        }
        select.is-invalid {
            border-color: red;
        }
    </style>
@endpush
<form id="form-list-of-prepared-computerized-result" action="{{ route('committee.input.review.prepare.computerized.result.store') }}" method="POST">
    @csrf
    <input type="hidden" id="sid" name="sid" value="{{$sid}}">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center">
                    <h2 class="card-title">
                        <span class="step-badge">8.d </span>
                        List of Teachers Prepared Computerized Result (@ **/- per student per subject)
                    </h2>
                </header>

                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="prepare_computerized_result_rate">Per Student Per Subject Rate</label>
                                <input type="number" name="prepare_computerized_result_rate" value="{{$prepared_computerized_per_student_per_subject_rate??12}}" step="any" class="form-control" placeholder="Enter per student per subject rate" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                        </div>
                        <div class="col-md-4 mb-4">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            @if(isset($all_course_with_teacher->courses))
                                @foreach($all_course_with_teacher->courses as $courseData)
                                    @php
                                        $single_course = $courseData->courseObject;
                                        $course_code = $single_course->courseno;
                                        $savedForPreparedComputerizedResult = $savedRateAssignPreparedComputerizedResult[$course_code] ?? collect(); // Collection of RateAssigns
                                    @endphp
                                    <!-- Hidden course-level metadata -->
                                    <input type="hidden" name="courseno[{{ $single_course->id }}]" value="{{ $single_course->courseno }}">
                                    <input type="hidden" name="coursetitle[{{ $single_course->id }}]" value="{{ $single_course->coursetitle }}">
                                    <input type="hidden" name="registered_students_count[{{ $single_course->id }}]" value="{{ $courseData->registered_students_count }}">

                                    <section class="card card-featured card-featured-secondary">
                                        <header class="card-header">
                                            <h2 class="card-title">
                                                Course: {{ $single_course->courseno }} - {{ $single_course->coursetitle }}
                                            </h2>
                                        </header>

                                        <div class="card-body card-list-of-prepared-computerized-result">
                                            <div class="row mb-3">
                                                <div class="col-md-9">
                                                    <label for="prepared_computerized_result_teacher_{{ $single_course->id }}_{{ $loop->index }}">Select Scrutinizers</label>
                                                    <select name="prepared_computerized_result_teacher_ids[{{ $single_course->id }}][]"
                                                            multiple data-plugin-selectTwo
                                                            id="prepared_computerized_result_teacher_{{ $single_course->id }}_{{ $loop->index }}"
                                                            class="form-control populate" required>
                                                        <option value="" disabled>-- Select Teacher --</option>
                                                        @foreach($groupedTeachers as $deptFullName => $deptTeachers)
                                                            <optgroup label="{{ $deptFullName }}">
                                                                @foreach($deptTeachers as $teacher)
                                                                    <option value="{{ $teacher->id }}" {{ $savedForPreparedComputerizedResult->pluck('teacher_id')->contains($teacher->id) ? 'selected' : '' }}>
                                                                        {{ $teacher->user->name }} - {{ $teacher->department->shortname }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-3">
                                                    @php
                                                        // Check if there is saved data, and if yes, get total_students from the first teacher's entry
                                                        $noOfItems = $savedForPreparedComputerizedResult->isNotEmpty()
                                                                    ? $savedForPreparedComputerizedResult->first()->total_students
                                                                    : $courseData->registered_students_count;
                                                    @endphp
                                                    <label for="prepared_computerized_result_no_of_students">Number of Students</label>
                                                    <input name="prepared_computerized_result_no_of_students[{{ $single_course->id }}]"
                                                           type="number" min="0" step="any"
                                                           class="form-control"
                                                           value="{{ old('prepared_computerized_result_no_of_students.' . $single_course->id, $noOfItems) }}"
                                                           required>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                @endforeach
                            @endif

                            <div class="text-end mt-3">
                                <button id="submit-list-of-prepared-computerized-result" type="submit" class="btn btn-primary">
                                    Submit Prepare Computerized Result Committee
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
            const form = document.getElementById('form-list-of-prepared-computerized-result');

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
                    if (result.isConfirmed) {
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
                                        throw new Error(err.message || 'Unknown error occurred.');
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log("Server response:", data);
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });

                                const submitBtn = document.getElementById('submit-list-of-prepared-computerized-result');
                                submitBtn.textContent = 'Update Prepare Computerized Result Committee';
                                submitBtn.classList.remove('btn-primary');
                                submitBtn.classList.add('btn-warning');

                                const cards = document.querySelectorAll('.card-list-of-prepared-computerized-result');

                                cards.forEach(card => {
                                    card.classList.add('fade-highlight');

                                    setTimeout(() => {
                                        card.classList.add('fade-out');
                                    }, 1000);

                                    setTimeout(() => {
                                        card.classList.remove('fade-highlight', 'fade-out');
                                    }, 1900);
                                });
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: error.message || 'Something went wrong. Please try again.',
                                    icon: 'error'
                                });
                            });
                    }
                });
            });
        });
    </script>
@endpush
