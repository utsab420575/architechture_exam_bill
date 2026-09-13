@push('styles')
    <style>
        .card-list-of-scrutinizers-theory-grade-sheet {
            background-color: white; /* starting point */
            transition: background-color 0.6s ease-in-out;
        }

        .card-list-of-scrutinizers-theory-grade-sheet.fade-highlight {
            background-color: #28a745; /* strong green */
        }

        .card-list-of-scrutinizers-theory-grade-sheet.fade-out {
            background-color: white;
        }
        select.is-invalid {
            border-color: red;
        }
    </style>

    {{--copy toggle style--}}
    <style>
        /* Pretty toggle + chip text */
        .copy-toggle {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            user-select: none;
            font-weight: 600;
        }
        .copy-toggle input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .copy-toggle .track {
            width: 56px;
            height: 30px;
            background: linear-gradient(to bottom, #eef2f7, #e7ecf2);
            border-radius: 999px;
            position: relative;
            box-shadow: inset 0 1px 2px rgba(0,0,0,.08);
            transition: all .25s ease;
        }
        .copy-toggle .knob {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,.18);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .copy-toggle:hover .track { filter: brightness(1.03); }

        .copy-toggle input:checked + .track {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            box-shadow: 0 0 0 4px rgba(34,197,94,.15);
        }
        .copy-toggle input:checked + .track .knob {
            transform: translateX(26px);
            box-shadow: 0 4px 10px rgba(22,163,74,.35);
        }

        .copy-toggle input:focus-visible + .track {
            outline: 2px solid #16a34a;
            outline-offset: 2px;
        }

        .copy-toggle .label-text {
            font-size: .95rem;
            color: #334155;
            padding: .25rem .6rem;
            border: 1px solid #e2e8f0;
            border-radius: .5rem;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
            transition: color .2s ease, border-color .2s ease, background .2s ease;
            white-space: nowrap;
        }
        .copy-toggle input:checked ~ .label-text {
            color: #167c3a;
            border-color: #86efac;
            background: linear-gradient(to bottom, #f0fdf4, #dcfce7);
        }

        /* Optional: keep header items on one line nicely spaced */
        .card-header.d-flex { gap: 14px; }
    </style>
@endpush
<form id="form-list-of-scrutinizers-theory-grade-sheet" action="{{ route('committee.input.special.scrutinizers.theory.grade.sheet.store') }}" method="POST">
    @csrf
    <input type="hidden" id="sid" name="sid" value="{{$sid}}">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center">
                    <h2 class="card-title mb-0">
                        <span class="step-badge">10.a</span>
                        List of Teachers for the Scrutinizing of Grade Sheet (Theoretical) (@**/- per student per subject)
                    </h2>

                    <label class="copy-toggle ms-auto" title="Copy teachers from Preparation (Theory) form">
                        <input type="checkbox" id="copy-in-scrutinizing-theory-grade-sheet">
                        <span class="track"><span class="knob"></span></span>
                        <span class="label-text">Same as “Preparation (Theory) Form”</span>
                    </label>
                </header>

                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="scrutinize_theory_grade_sheet_rate">Per Student Per Subject Rate</label>
                                <input type="number"  name="scrutinize_theory_grade_sheet_rate" value="{{$scrunizing_theory_grade_sheet_per_subject_rate??20}}" step="any" class="form-control" placeholder="Enter per student per subject rate" required>
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
				                         $savedForScrutinizersTheoryGradeSheet = $savedRateAssignScrutinizersTheoryGradeSheet[$course_code] ?? collect(); // Collection of RateAssigns
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

                                        <div class="card-body card-list-of-scrutinizers-theory-grade-sheet">
                                            <div class="row mb-3">
                                                <div class="col-md-9">
                                                    <label for="scrutinizing_theory_grade_sheet_teacher_{{ $single_course->id }}_{{ $loop->index }}">Select Scrutinizers</label>
                                                    <select name="scrutinizing_theory_grade_sheet_teacher_ids[{{ $single_course->id }}][]"
                                                            multiple data-plugin-selectTwo
                                                            id="prepare_theory_grade_sheet_teacher_{{ $single_course->id }}_{{ $loop->index }}"
                                                            class="form-control  populate"  required>
                                                        <option value="" disabled>-- Select Teacher --</option>
                                                        @foreach($groupedTeachers as $deptFullName => $deptTeachers)
                                                            <optgroup label="{{ $deptFullName }}">
                                                                @foreach($deptTeachers as $teacher)
                                                                    <option value="{{ $teacher->id }}" {{ $savedForScrutinizersTheoryGradeSheet->pluck('teacher_id')->contains($teacher->id) ? 'selected' : '' }}>
                                                                        {{ $teacher->user->name }}  - {{ $teacher->department->shortname }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                </div>


                                                <div class="col-md-3">
                                                    @php
                                                        // Check if there is saved data, and if yes, get total_students from the first teacher's entry
                                                        $noOfItems = $savedForScrutinizersTheoryGradeSheet->isNotEmpty()
                                                                    ? $savedForScrutinizersTheoryGradeSheet->first()->total_students
                                                                    : $courseData->registered_students_count;
                                                    @endphp
                                                    <label for="scrutinizing_theory_grade_sheet_no_of_students">Per Script Rate</label>
                                                    <input name="scrutinizing_theory_grade_sheet_no_of_students[{{ $single_course->id }}]"
                                                           type="number" min="0" step="any"
                                                           class="form-control"
                                                           {{--value="{{ $courseData->registered_students_count }}"--}}
                                                           value="{{ old('scrutinizing_theory_grade_sheet_no_of_students.' . $single_course->id, $noOfItems) }}"
                                                           >
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                @endforeach
                            @endif

                            <div class="text-end mt-3">
                                <button id="submit-list-of-scrutinizers-theory-grade-sheet" type="submit" class="btn btn-primary">
                                    Submit Theoretical Scrutinizing Committee
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
        document.addEventListener('DOMContentLoaded', () => {
            wireCopyAcrossForms({
                srcTeacherPrefix: 'prepares_theory_grade_sheet_teacher_ids',
                srcCountPrefix:   'prepares_theory_grade_sheet_no_of_students',
                dstTeacherPrefix: 'scrutinizing_theory_grade_sheet_teacher_ids',
                dstCountPrefix:   'scrutinizing_theory_grade_sheet_no_of_students',
                checkboxId:       'copy-in-scrutinizing-theory-grade-sheet'
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('form-list-of-scrutinizers-theory-grade-sheet');

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                // ✅ Validate teacher selections
                const teacherSelects = form.querySelectorAll('select[name^="teachers"]');
                let allSelected = true;

                teacherSelects.forEach(select => {
                    if (select.selectedOptions.length === 0) {
                        allSelected = false;
                        select.classList.add('is-invalid'); // red border if invalid
                    } else {
                        select.classList.remove('is-invalid');
                    }
                });

                if (!allSelected) {
                    Swal.fire({
                        title: 'Missing Teacher',
                        text: 'Please select at least one teacher for each course.',
                        icon: 'warning'
                    });
                    return; // ❌ stop form submission
                }

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

                                const submitBtn = document.getElementById('submit-list-of-scrutinizers-theory-grade-sheet');
                                submitBtn.textContent = 'Update Theoretical Scrutinizing Committee';  // ✅ New label
                                submitBtn.classList.remove('btn-primary');
                                submitBtn.classList.add('btn-warning');

                                const cards = document.querySelectorAll('.card-list-of-scrutinizers-theory-grade-sheet');

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

