@push('styles')
    <style>
        .card-list-of-conducted-central-oral-examination {
            background-color: white;
            transition: background-color 0.6s ease-in-out;
        }

        .card-list-of-conducted-central-oral-examination.fade-highlight {
            background-color: #28a745;
        }

        .card-list-of-conducted-central-oral-examination.fade-out {
            background-color: white;
        }

        select.is-invalid, input.is-invalid {
            border-color: red;
        }
    </style>
@endpush

<form id="form-list-of-conducted-central-oral-examination" action="{{ route('committee.input.conducted.central.oral.exam.store') }}" method="POST">
    @csrf
    <input type="hidden" value="{{$sid}}" name="sid">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center">
                    <h2 class="card-title">
                        <span class="step-badge">7.e</span>
                        List of Teachers conducted central oral examination/Jury of thesis/projects (@***/- thesis/projects)</h2>
                </header>

                <div class="card-body card-list-of-conducted-central-oral-examination">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="oral_central_exam_thesis_project">Per Student Per Thesis/Project Rate</label>
                                <input type="number"  name="oral_central_exam_thesis_project" value="{{$conducted_central_oral_per_thesis_rate??150}}" step="any" class="form-control" placeholder="Enter per student per thesis/proejct rate" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                        </div>
                        <div class="col-md-4 mb-4">
                        </div>
                    </div>


                    <div class="row mb-2 fw-bold mt-2">
                        <div class="col-md-8 text-start">Select Teacher</div>
                        <div class="col-md-3 text-start" style="margin-left:0px;">No of Students</div>
                    </div>

                    {{--here will be add checkbox--}}
                    <div id="dynamic-conducted-central-oral-examination-container"></div>

                    <div class="mt-3 text-end">
                        <button type="button" id="add-conducted-central-oral-examination-row" class="btn btn-sm btn-success me-2">+ Add Teacher</button>
                    </div>

                    <div class="text-end mt-3">
                        <button id="submit-list-of-conducted-central-oral-examination" type="submit" class="btn btn-primary">
                            Submit Conducted Central Oral Examination
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        (function() {
            // Local variables (scoped to this function only)
            let rowCount = 0;
            const allTeachers = @json($teachers ?? []);
            const savedData = @json($savedRateAssignConductedCentralOralExam ?? null);

            console.log('Teachers:', allTeachers);
            console.log('Saved Data:', savedData);

            /**
             * Create a new row with teacher select and student amount input
             */
            function createRow(selectedTeacherIds = [], studentAmount = '') {
                rowCount++;
                const container = document.getElementById('dynamic-conducted-central-oral-examination-container');

                const row = document.createElement('div');
                row.className = 'row align-items-center mb-2';
                row.setAttribute('data-row-id', rowCount);

                let teacherOptions = '';
                allTeachers.forEach(teacher => {
                    const isSelected = selectedTeacherIds.includes(String(teacher.id)) ? 'selected' : '';
                    const teacherName = teacher.user?.name || 'Unknown Teacher';
                    const deptName = teacher.department?.shortname ? `-${teacher.department.shortname}` : '';
                    teacherOptions += `<option value="${teacher.id}" ${isSelected}>${teacherName}${deptName}</option>`;
                });

                row.innerHTML = `
            <div class="col-md-8">
                <select name="conducted_central_oral_examination_teacher_ids[${rowCount}][]"
                        class="form-control teacher-select-conducted_central_oral_examination_student_amounts"
                        multiple required>
                    ${teacherOptions}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number"
                       name="conducted_central_oral_examination_student_amounts[${rowCount}]"
                       class="form-control"
                       placeholder="No of students"
                       value="${studentAmount}"
                       required min="1">
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-btn">🗑️</button>
            </div>
        `;

                container.appendChild(row);

                if (window.$ && $.fn.select2) {
                    $(row).find('.teacher-select-conducted_central_oral_examination_student_amounts').select2({
                        theme: 'bootstrap',
                        width: '100%',
                        placeholder: '-- Select Teacher(s) --',
                        allowClear: true,
                        closeOnSelect: false
                    });
                }

                row.querySelector('.remove-btn').addEventListener('click', function() {
                    row.remove();
                });

                console.log(`Created row ${rowCount} with teachers:`, selectedTeacherIds, 'students:', studentAmount);
            }

            /**
             * Load saved data
             */
            function loadSavedData() {
                if (!savedData) return;
                if (savedData.full_grouped_data && savedData.grouped_keys) {
                    savedData.grouped_keys.forEach(groupKey => {
                        const groupRecords = savedData.full_grouped_data[groupKey] || [];
                        if (groupRecords.length === 0) return;
                        const teacherIds = groupRecords.map(record => String(record.teacher_id));
                        const studentAmount = groupRecords[0].total_students || '';
                        createRow(teacherIds, studentAmount);
                    });
                } else if (Array.isArray(savedData)) {
                    const groups = {};
                    savedData.forEach(record => {
                        const key = record.total_students || 'default';
                        if (!groups[key]) groups[key] = [];
                        groups[key].push(record);
                    });
                    Object.values(groups).forEach(groupRecords => {
                        const teacherIds = groupRecords.map(record => String(record.teacher_id));
                        const studentAmount = groupRecords[0].total_students || '';
                        createRow(teacherIds, studentAmount);
                    });
                }
            }

            /**
             * Validate form
             */
            function validateForm() {
                const teacherSelects = document.querySelectorAll('.teacher-select-conducted_central_oral_examination_student_amounts');
                const studentInputs = document.querySelectorAll('input[name^="conducted_central_oral_examination_student_amounts"]');
                let isValid = true;
                teacherSelects.forEach((select, index) => {
                    const selectedTeachers = Array.from(select.selectedOptions).map(option => option.value);
                    const studentAmount = studentInputs[index]?.value || '';
                    select.classList.remove('is-invalid');
                    studentInputs[index]?.classList.remove('is-invalid');
                    if (selectedTeachers.length === 0) {
                        select.classList.add('is-invalid');
                        isValid = false;
                    }
                    if (!studentAmount || Number(studentAmount) <= 0) {
                        studentInputs[index]?.classList.add('is-invalid');
                        isValid = false;
                    }
                });
                return isValid;
            }

            /**
             * Submit form via AJAX
             */
            function submitForm(form) {
                const formData = new FormData(form);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch(form.action, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': csrfToken},
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire('Success!', data.message || 'Data saved successfully', 'success');
                        const submitBtn = document.getElementById('submit-list-of-conducted-central-oral-examination');
                        submitBtn.textContent = 'Update Conducted Central Oral Examination';
                        submitBtn.classList.remove('btn-primary');
                        submitBtn.classList.add('btn-warning');
                        document.querySelectorAll('.card-list-of-conducted-central-oral-examination').forEach(card => {
                            card.classList.add('fade-highlight');
                            setTimeout(() => card.classList.add('fade-out'), 1000);
                            setTimeout(() => card.classList.remove('fade-highlight', 'fade-out'), 1900);
                        });
                    })
                    .catch(error => {
                        Swal.fire('Error!', error.message || 'Something went wrong', 'error');
                    });
            }

            // Initialize
            document.addEventListener('DOMContentLoaded', function() {
                loadSavedData();
                document.getElementById('add-conducted-central-oral-examination-row')
                    .addEventListener('click', () => createRow());
                document.getElementById('form-list-of-conducted-central-oral-examination')
                    .addEventListener('submit', function(e) {
                        e.preventDefault();
                        if (!validateForm()) {
                            Swal.fire('Validation Error', 'Please select teachers and enter student amounts for all rows', 'error');
                            return;
                        }
                        Swal.fire({
                            title: 'Confirm Save',
                            text: 'Do you want to save the committee data?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Save!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) submitForm(this);
                        });
                    });
            });
        })();
    </script>
@endpush



