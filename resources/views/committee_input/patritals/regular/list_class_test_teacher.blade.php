@push('styles')
    <style>
        .card-list-of-class-test-teacher {
            background-color: white;
            transition: background-color 0.6s ease-in-out;
        }

        .card-list-of-class-test-teacher.fade-highlight {
            background-color: #28a745;
        }

        .card-list-of-class-test-teacher.fade-out {
            background-color: white;
        }

        select.is-invalid, input.is-invalid {
            border-color: red;
        }
    </style>
@endpush
<form id="form-list-of-class-test-teacher" action="{{ route('committee.input.regular.class.test.teacher.store') }}" method="POST">
    @csrf
    <input type="hidden" id="sid" name="sid" value="{{$sid}}">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center">

                    <h2 class="card-title">
                        <span class="step-badge">4</span>Internal Assessment/Class Test @**/- per class test per student
                    </h2>
                </header>
                <div class="card-body">
                    <div class="row mb-2">
                            <div class="col-md-4 mb-4">
                                <div class="form-group">
                                    <label for="class_test_rate">Per Class Test Rate</label>
                                    <input type="number"  name="class_test_rate" value="{{$ct_per_class_test_rate??60}}" step="any" class="form-control" placeholder="Enter class test rate" required>


                                </div>
                            </div>
                    </div>
                    <div class="row">
                        @if(isset($all_course_with_teacher->courses))
                            @foreach($all_course_with_teacher->courses as $courseData)
                                @php
                                    $single_course = $courseData->courseObject;

                                    $course_code = $single_course->courseno;
                                     $savedForClassTest = $savedRateAssignClassTest[$course_code] ?? collect(); // Collection of RateAssigns
                                     //dump($savedForClassTest);

                                @endphp

                                    <!-- Hidden course-level metadata -->
                                <input type="hidden" name="courseno[{{ $single_course->id }}]" value="{{ $single_course->courseno }}">
                                <input type="hidden" name="coursetitle[{{ $single_course->id }}]" value="{{ $single_course->coursetitle }}">
                               {{-- <input type="hidden" name="registered_students_count[{{ $single_course->id }}]" value="{{ $courseData->registered_students_count }}">--}}
                                <input type="hidden" name="teacher_count[{{ $single_course->id }}]" value="{{ count($single_course->teachers) }}">

                                <section class="card card-featured card-featured-secondary mb-4 w-100">
                                    <header class="card-header">
                                        <h2 class="card-title">
                                            Course: {{ $single_course->courseno }} - {{ $single_course->coursetitle }}
                                        </h2>
                                    </header>

                                    <div class="card-body card-list-of-class-test-teacher">
                                        <div class="row">
                                            <div class="col-md-8 ms-2">
                                                Teacher Name
                                            </div>

                                             <!-- Left Side: Paper Setter & Examiner -->
                                            <div class="col-md-8">
                                                <div class="p-2" id="class-test-teachers-container-{{ $single_course->id }}">
                                                    @php
                                                        $savedCount = $savedForClassTest->count();
                                                        $apiTeachers = $single_course->teachers ?? collect();
                                                        $totalRows = max($savedCount, count($apiTeachers));
                                                        if ($totalRows == 0) { $totalRows = 1; }
                                                    @endphp

                                                    @for($index = 0; $index < $totalRows; $index++)
                                                        @php
                                                            $assignedTeacher = $apiTeachers[$index] ?? null;
                                                            $savedTeacherId = $savedForClassTest->values()[$index]->teacher_id ?? null;
                                                        @endphp
                                                        <div class="row mb-3 align-items-center ct-teacher-row">
                                                            <div class="col-md-10">
                                                                <select name="class_test_teachers_ids[{{ $single_course->id }}][]"
                                                                        id="class_test_teachers_ids{{ $single_course->id }}_{{ $index }}"
                                                                        data-plugin-selectTwo
                                                                        class="form-control populate" required>
                                                                    <option value="">-- Select Teacher --</option>
                                                                    @foreach($teachers as $teacherOption)
                                                                        @php
                                                                            if ($savedForClassTest->isNotEmpty()) {
                                                                                $isSelected = (int) $teacherOption->id === (int) $savedTeacherId;
                                                                            } else {
                                                                                $isSelected = isset($assignedTeacher->user->email, $teacherOption->user->email) &&
                                                                                              $assignedTeacher->user->email === $teacherOption->user->email;
                                                                            }
                                                                        @endphp
                                                                        <option value="{{ $teacherOption->id }}"
                                                                            {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ $teacherOption->user->name }} - {{ $teacherOption->designation->designation }} - {{ $teacherOption->department->shortname }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-2 text-end">
                                                                <button type="button" class="btn btn-sm btn-danger btn-remove-ct-row" title="Remove Teacher">🗑️</button>
                                                            </div>
                                                        </div>
                                                    @endfor
                                                </div>

                                                <!-- Course-wise Add Teacher Button -->
                                                <div class="mt-2 ms-2">
                                                    <button type="button" class="btn btn-sm btn-outline-success btn-add-class-test-teacher"
                                                            data-course-id="{{ $single_course->id }}">
                                                        + Add Teacher
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Right Side: No of Scripts -->
                                            <div class="col-md-4 d-flex align-items-center justify-content-center">
                                                <div class="form-group w-100">
                                                    <label for="no_of_students_ct_{{ $single_course->id }}">No of Students</label>
                                                    @php
                                                        // Prefer database-saved script count, fallback to API count
                                                        $noOfScript = $savedForClassTest->first()->total_students ?? $courseData->registered_students_count;
                                                    @endphp

                                                    <input type="number"
                                                           id="no_of_students_ct_{{ $single_course->id }}"
                                                           name="no_of_students_ct[{{ $single_course->id }}]"
                                                           class="form-control"
                                                           min="0"
                                                           step="any"
                                                           value="{{ old('no_of_script.'.$single_course->id, $noOfScript) }}"
                                                           required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            @endforeach
                        @endif

                    </div>

                    <div class="text-end mt-3">
                        <button id="submit-list-of-class-test-teacher" type="submit" class="btn btn-primary">
                            Submit Class Test Teacher
                        </button>
                    </div>
                </div>

            </section>
        </div>
    </div>
</form>


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('form-list-of-class-test-teacher');
            const allTeachers = @json($teachers);

            // Dynamic course-wise Add Teacher button for Class Test
            document.addEventListener('click', function (e) {
                const addBtn = e.target.closest('.btn-add-class-test-teacher');
                if (addBtn) {
                    const courseId = addBtn.getAttribute('data-course-id');
                    const container = document.getElementById(`class-test-teachers-container-${courseId}`);
                    if (!container) return;

                    const rowDiv = document.createElement('div');
                    rowDiv.classList.add('row', 'mb-3', 'align-items-center', 'ct-teacher-row');

                    let teacherOptionsHtml = '<option value="">-- Select Teacher --</option>';
                    allTeachers.forEach(t => {
                        const name = t.user ? t.user.name : (t.teachername || '');
                        const desig = t.designation ? t.designation.designation : '';
                        const dept = t.department ? t.department.shortname : '';
                        teacherOptionsHtml += `<option value="${t.id}">${name} - ${desig} - ${dept}</option>`;
                    });

                    rowDiv.innerHTML = `
                        <div class="col-md-10">
                            <select name="class_test_teachers_ids[${courseId}][]" class="form-control populate dynamic-select2-ct" required>
                                ${teacherOptionsHtml}
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-sm btn-danger btn-remove-ct-row" title="Remove Teacher">🗑️</button>
                        </div>
                    `;

                    container.appendChild(rowDiv);

                    // Initialize Select2 on the new dropdown
                    $(rowDiv).find('.dynamic-select2-ct').select2({
                        theme: 'bootstrap',
                        width: '100%',
                        allowClear: true,
                        placeholder: '-- Select Teacher --'
                    });
                }

                // Dynamic Remove Row button
                const removeBtn = e.target.closest('.btn-remove-ct-row');
                if (removeBtn) {
                    const row = removeBtn.closest('.ct-teacher-row');
                    const container = row.parentElement;
                    if (container && container.querySelectorAll('.ct-teacher-row').length > 1) {
                        row.remove();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Notice',
                            text: 'At least one teacher row must remain for each course.'
                        });
                    }
                }
            });

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
                                    // Return the error JSON and throw it
                                    return response.json().then(err => {
                                        throw new Error(err.message || 'Unknown error occurred.');
                                    });
                                }
                                return response.json(); // if response is OK
                            })
                            .then(data => {
                                console.log("Server response:", data); // Debug log
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });

                                const submitBtn = document.getElementById('submit-list-of-class-test-teacher');
                                submitBtn.textContent = 'Update Class Test Teacher';  // ✅ New label
                                submitBtn.classList.remove('btn-primary');
                                submitBtn.classList.add('btn-warning');

                                const cards = document.querySelectorAll('.card-list-of-class-test-teacher');
                                cards.forEach(card => {
                                    card.classList.add('fade-highlight');
                                    setTimeout(() => card.classList.add('fade-out'), 1000);
                                    setTimeout(() => card.classList.remove('fade-highlight', 'fade-out'), 1900);
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

