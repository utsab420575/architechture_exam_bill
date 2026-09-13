<form id="form-list-of-sessional-course-teacher"
      action="{{ route('committee.input.regular.sessional.course.teacher.store') }}" method="POST">
    @csrf
    <input type="hidden" id="sid" name="sid" value="{{$sid}}">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center">
                    <h2 class="card-title">
                        <span class="step-badge">5</span>
                        Sessional (@ ***/- per contact hour per week; min ****/- per examiner)
                    </h2>
                </header>

                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6 mb-4">
                            <div class="form-group">
                                <label for="sessional_per_hour_rate">Per Contact Hour Rate</label>
                                <input type="number" name="sessional_per_hour_rate" step="any" value="{{$sessional_per_contact_hour_rate??115}}"
                                       class="form-control" placeholder="Enter per contact hour rate" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="form-group">
                                <label for="sessional_examiner_min_rate">Minimum Examiner Rate</label>
                                <input type="number" name="sessional_examiner_min_rate" value="{{$sessional_min_exam_rate??1600}}" step="any"
                                       class="form-control" placeholder="Enter minimum examiner rate" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            @if(isset($all_sessional_course_with_teacher->courses))
                                @foreach($all_sessional_course_with_teacher->courses as $courseData)
                                    @php
                                        $single_course = $courseData->courseObject;
                                        $course_code = $single_course->courseno;
					                    $savedForSessionalCourseTeacher = $savedRateAssignSessionalCourseTeacher[$course_code] ?? collect(); // Collection of RateAssigns
                                        $courseStudentsCount = $savedForSessionalCourseTeacher->first()->total_students ?? ($courseData->registered_students_count ?? 30);
                                    @endphp

                                        <!-- Hidden course-level metadata -->
                                    <input type="hidden" name="courseno[{{ $single_course->id }}]"
                                           value="{{ $single_course->courseno }}">
                                    <input type="hidden" name="coursetitle[{{ $single_course->id }}]"
                                           value="{{ $single_course->coursetitle }}">
                                    <input type="hidden" name="teacher_count[{{ $single_course->id }}]"
                                           value="{{ max(1, count($single_course->teachers)) }}">

                                    <section class="card card-featured card-featured-secondary mb-4 w-100">
                                        <header class="card-header d-flex justify-content-between align-items-center">
                                            <h2 class="card-title mb-0">
                                                Course: {{ $single_course->courseno }}
                                                - {{ $single_course->coursetitle }}
                                            </h2>
                                            <div class="d-flex align-items-center">
                                                <label for="no_of_students_sessional_{{ $single_course->id }}" class="me-2 mb-0 fw-bold" style="white-space: nowrap;">
                                                    Total Students:
                                                </label>
                                                <input type="number"
                                                       id="no_of_students_sessional_{{ $single_course->id }}"
                                                       name="no_of_students_sessional[{{ $single_course->id }}]"
                                                       class="form-control form-control-sm"
                                                       style="width: 100px;"
                                                       min="0"
                                                       step="any"
                                                       value="{{ old('no_of_students_sessional.'.$single_course->id, $courseStudentsCount) }}"
                                                       required>
                                            </div>
                                        </header>

                                        <div class="card-body">
                                            <table
                                                class="table-list-of-sessional-course-teacher table table-responsive-md table-striped mb-0">
                                                <thead>
                                                <tr>
                                                    <th style="width: 55%;">Name</th>
                                                    <th style="width: 30%;">Contact Hour/Week</th>
                                                    <th style="width: 15%; text-align: center;">Action</th>
                                                </tr>
                                                </thead>
                                                <tbody id="sessional-teachers-tbody-{{ $single_course->id }}">
                                                    @php
                                                        $savedCount = $savedForSessionalCourseTeacher->count();
                                                        $apiTeachers = $single_course->teachers ?? collect();
                                                        $totalRows = max($savedCount, count($apiTeachers));
                                                        if ($totalRows == 0) { $totalRows = 1; }
                                                        $defaultContactHours = $single_course->credithour ? $single_course->credithour * 2 : '';
                                                    @endphp

                                                    @for($index = 0; $index < $totalRows; $index++)
                                                        @php
                                                            $assignedTeacher = $apiTeachers[$index] ?? null;
                                                            $savedRecord = $savedForSessionalCourseTeacher->values()[$index] ?? null;
                                                            $savedTeacherId = $savedRecord ? $savedRecord->teacher_id : null;
                                                            $contactHourVal = $savedRecord ? $savedRecord->no_of_items : $defaultContactHours;
                                                        @endphp
                                                        <tr class="sessional-teacher-row">
                                                            <td>
                                                                <select
                                                                    name="sessional_course_teacher_ids[{{ $single_course->id }}][]"
                                                                    data-plugin-selectTwo
                                                                    class="form-control populate" required>
                                                                    <option value="">-- Select Teacher --</option>
                                                                    @foreach($teachers as $teacherOption)
                                                                        @php
                                                                            if ($savedForSessionalCourseTeacher->isNotEmpty()) {
                                                                                $isSelected = (int) $teacherOption->id === (int) $savedTeacherId;
                                                                            } else {
                                                                                $isSelected = isset($assignedTeacher->user->email, $teacherOption->user->email) &&
                                                                                              $assignedTeacher->user->email === $teacherOption->user->email;
                                                                            }
                                                                        @endphp

                                                                        <option value="{{ $teacherOption->id }}"
                                                                            {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ $teacherOption->user->name }}
                                                                            - {{ $teacherOption->designation->designation }}
                                                                            - {{ $teacherOption->department->shortname }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input
                                                                    name="no_of_contact_hour[{{ $single_course->id }}][]"
                                                                    type="number" min="0" step="any"
                                                                    class="form-control"
                                                                    value="{{ old('no_of_contact_hour.'.$single_course->id.'.'.$index, $contactHourVal) }}"
                                                                    required>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <button type="button" class="btn btn-sm btn-danger btn-remove-sessional-row" title="Remove Teacher">🗑️</button>
                                                            </td>
                                                        </tr>
                                                    @endfor
                                                </tbody>
                                            </table>

                                            <!-- Course-wise Add Teacher Button -->
                                            <div class="mt-2 text-start">
                                                <button type="button" class="btn btn-sm btn-outline-success btn-add-sessional-teacher"
                                                        data-course-id="{{ $single_course->id }}"
                                                        data-default-hours="{{ $defaultContactHours }}">
                                                    + Add Teacher
                                                </button>
                                            </div>
                                        </div>
                                    </section>
                                @endforeach
                            @endif

                            <div class="text-end mt-3">
                                <button id="submit-list-of-sessional-course-teacher" type="submit"
                                        class="btn btn-primary">
                                    Submit Sessional Examiner
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
            const form = document.getElementById('form-list-of-sessional-course-teacher');
            const allTeachers = @json($teachers);

            // Dynamic course-wise Add Teacher button for Sessional
            document.addEventListener('click', function (e) {
                const addBtn = e.target.closest('.btn-add-sessional-teacher');
                if (addBtn) {
                    const courseId = addBtn.getAttribute('data-course-id');
                    const defaultHours = addBtn.getAttribute('data-default-hours') || '';
                    const tbody = document.getElementById(`sessional-teachers-tbody-${courseId}`);
                    if (!tbody) return;

                    const tr = document.createElement('tr');
                    tr.classList.add('sessional-teacher-row');

                    let teacherOptionsHtml = '<option value="">-- Select Teacher --</option>';
                    allTeachers.forEach(t => {
                        const name = t.user ? t.user.name : (t.teachername || '');
                        const desig = t.designation ? t.designation.designation : '';
                        const dept = t.department ? t.department.shortname : '';
                        teacherOptionsHtml += `<option value="${t.id}">${name} - ${desig} - ${dept}</option>`;
                    });

                    tr.innerHTML = `
                        <td>
                            <select name="sessional_course_teacher_ids[${courseId}][]" class="form-control populate dynamic-select2-sessional" required>
                                ${teacherOptionsHtml}
                            </select>
                        </td>
                        <td>
                            <input name="no_of_contact_hour[${courseId}][]" type="number" min="0" step="any" class="form-control" value="${defaultHours}" required>
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-danger btn-remove-sessional-row" title="Remove Teacher">🗑️</button>
                        </td>
                    `;

                    tbody.appendChild(tr);

                    // Initialize Select2 on the new dropdown
                    $(tr).find('.dynamic-select2-sessional').select2({
                        theme: 'bootstrap',
                        width: '100%',
                        allowClear: true,
                        placeholder: '-- Select Teacher --'
                    });
                }

                // Dynamic Remove Row button
                const removeBtn = e.target.closest('.btn-remove-sessional-row');
                if (removeBtn) {
                    const tr = removeBtn.closest('.sessional-teacher-row');
                    const tbody = tr.parentElement;
                    if (tbody && tbody.querySelectorAll('.sessional-teacher-row').length > 1) {
                        tr.remove();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Notice',
                            text: 'At least one teacher row must remain for each sessional course.'
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

                                const submitBtn = document.getElementById('submit-list-of-sessional-course-teacher');
                                submitBtn.textContent = 'Update Sessional Examiner';  // ✅ New label
                                submitBtn.classList.remove('btn-primary');
                                submitBtn.classList.add('btn-warning');

                                const cells = document.querySelectorAll('.table-list-of-sessional-course-teacher td');

                                cells.forEach(td => {
                                    td.classList.add('fade-green');

                                    // Start fade out after short delay
                                    setTimeout(() => {
                                        td.classList.add('fade-out');
                                    }, 1000);

                                    // Remove classes to reset
                                    setTimeout(() => {
                                        td.classList.remove('fade-green', 'fade-out');
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

