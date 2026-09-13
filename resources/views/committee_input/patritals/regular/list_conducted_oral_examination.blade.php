@push('styles')
    <style>
        .card-list-of-conducted-oral-examination {
            background-color: white;
            transition: background-color 0.6s ease-in-out;
        }

        .card-list-of-conducted-oral-examination.fade-highlight {
            background-color: #28a745;
        }

        .card-list-of-conducted-oral-examination.fade-out {
            background-color: white;
        }

        select.is-invalid, input.is-invalid {
            border-color: red;
        }
    </style>
@endpush

<form id="form-list-of-conducted-oral-examination" action="{{ route('committee.input.conducted.oral.examination.store') }}" method="POST">
    @csrf
    <input type="hidden" value="{{$sid}}" name="sid">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center">
                    <h2 class="card-title">
                        <span class="step-badge">6.d</span>
                        List of Teachers conducted oral examination/Jury of thesis/projects (@***/- thesis/projects)</h2>
                </header>

                <div class="card-body card-list-of-conducted-oral-examination">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="oral_exam_thesis_project">Per Student Per Thesis/Project Rate</label>
                                <input type="number"  name="oral_exam_thesis_project" step="any" value="{{$conducted_oral_per_student_rate??225}}" class="form-control" placeholder="Enter per student per thesis/proejct rate" required>
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
                    <div id="dynamic-conducted-oral-examination-container"></div>

                    <div class="mt-3 text-end">
                        <button type="button" id="add-conducted-oral-examination-row" class="btn btn-sm btn-success me-2">+ Add Teacher</button>
                    </div>

                    <div class="text-end mt-3">
                        <button id="submit-list-of-conducted-oral-examination" type="submit" class="btn btn-primary">
                            Submit Conducted Oral Examination Committee
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        let conductedOralExaminationRowCount = 0;
        const conductedOralExaminationTeachers = @json($teachers);
        const savedConductedOralExaminationAssign = @json($savedRateAssignConductedOralExamination);

        function createConductedOralExaminationRow(teacherId = '', numberOfItems = '') {
            conductedOralExaminationRowCount++;

            const container = document.getElementById('dynamic-conducted-oral-examination-container');
            const row = document.createElement('div');
            row.classList.add('row', 'align-items-center', 'mb-2');
            row.setAttribute('data-row', conductedOralExaminationRowCount);

            row.innerHTML = `
                <div class="col-md-8">
                    <select name="conducted_oral_examination_teacher_ids[]" data-plugin-selectTwo class="form-control teacher-select populate" data-row="${conductedOralExaminationRowCount}" required>
                        <option value="">-- Select Teacher --</option>
                        ${conductedOralExaminationTeachers.map(t => `<option value="${t.id}" ${t.id == teacherId ? 'selected' : ''}>${t.user.name}, ${t.designation.designation}, ${t.department.shortname}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" name="conducted_oral_examination_student_amounts[]" class="form-control amount-input" placeholder="No of students" value="${numberOfItems}" required min="1">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-danger remove-row">🗑️</button>
                </div>
            `;

            container.appendChild(row);

            // Re-initialize Select2 for the newly added select input
            $(row).find('select').select2({
                theme: 'bootstrap',
                width: '100%',
                allowClear: true,
                placeholder: '-- Select Teacher --'
            });

            // Remove row logic
            row.querySelector('.remove-row').addEventListener('click', function () {
                row.remove();
            });
        }

        // Load pre-filled rows from DB if any data exists
        if (savedConductedOralExaminationAssign && savedConductedOralExaminationAssign.length > 0) {
            savedConductedOralExaminationAssign.forEach(assign => {
                createConductedOralExaminationRow(assign.teacher_id, assign.no_of_items);
            });
        }

        // Add new blank row
        document.getElementById('add-conducted-oral-examination-row').addEventListener('click', function () {
            createConductedOralExaminationRow();
        });

        // Form submission logic
        document.getElementById('form-list-of-conducted-oral-examination').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
            const selects = form.querySelectorAll('.teacher-select');
            const inputs = form.querySelectorAll('.amount-input');
            let valid = true;
            let teacherIds = [];

            selects.forEach((select, index) => {
                const teacherId = select.value;
                const amount = inputs[index].value;

                select.classList.remove('is-invalid');
                inputs[index].classList.remove('is-invalid');

                if (!teacherId) {
                    select.classList.add('is-invalid');
                    valid = false;
                }

                if (!amount || amount <= 0) {
                    inputs[index].classList.add('is-invalid');
                    valid = false;
                }

                if (teacherIds.includes(teacherId)) {
                    select.classList.add('is-invalid');
                    valid = false;
                }

                teacherIds.push(teacherId);
            });

            if (!valid) {
                Swal.fire('Validation Failed', 'Make sure each selected row is complete and teachers are not duplicated.', 'error');
                return;
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
                                return response.json().then(err => {
                                    throw new Error(err.message || 'Unknown error occurred.');
                                });
                            }
                            return response.json(); // if response is OK
                        })
                        .then(data => {
                            Swal.fire('Success!', data.message, 'success');

                            const submitBtn = document.getElementById('submit-list-of-conducted-oral-examination');
                            submitBtn.textContent = 'Update Conducted Oral Examination Committee';
                            submitBtn.classList.remove('btn-primary');
                            submitBtn.classList.add('btn-warning');

                            const cards = document.querySelectorAll('.card-list-of-conducted-oral-examination');
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
                                text: error.message||'Something went wrong. Please try again.',
                                icon: 'error'
                            });
                        });
                }
            });
        });
    </script>
@endpush

