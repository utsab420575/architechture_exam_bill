@push('styles')
    <style>
        .card-list-of-involved-survey {
            background-color: white;
            transition: background-color 0.6s ease-in-out;
        }

        .card-list-of-involved-survey.fade-highlight {
            background-color: #28a745;
        }

        .card-list-of-involved-survey.fade-out {
            background-color: white;
        }

        select.is-invalid, input.is-invalid {
            border-color: red;
        }
    </style>
@endpush

<form id="form-list-of-involved-survey" action="{{ route('committee.input.involved.survey.store') }}" method="POST">
    @csrf
    <input type="hidden" name="sid" value="{{$sid}}">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center">
                    <h2 class="card-title">
                        <span class="step-badge">7.f</span>
                        List of teachers involved survey (@ ***/- per student)</h2>
                </header>

                <div class="card-body card-list-of-involved-survey">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="servey_rate">Per Student Servey Rate</label>
                                <input type="number"  name="servey_rate" value="{{$involved_survey_per_student_rate??900}}" step="any" class="form-control" placeholder="Enter per student per servey rate" required>
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
                    <div id="dynamic-involved-survey-container"></div>

                    <div class="mt-3 text-end">
                        <button type="button" id="add-involved-survey-row" class="btn btn-sm btn-success me-2">+ Add Teacher</button>
                    </div>

                    <div class="text-end mt-3">
                        <button id="submit-list-of-involved-survey" type="submit" class="btn btn-primary">
                            Submit Involved Survey Committee
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        let involvedSurveyRowCount = 0;
        const involvedSurveyTeachers = @json($teachers);
        const savedInvolvedSurveyAssign = @json($savedRateAssignInvolvedSurvey);

        // Function to create a new row with teacher and amount
        function createInvolvedSurveyRow(teacherId = '', amount = '') {
            involvedSurveyRowCount++;

            const container = document.getElementById('dynamic-involved-survey-container');
            const row = document.createElement('div');
            row.classList.add('row', 'align-items-center', 'mb-2');
            row.setAttribute('data-row', involvedSurveyRowCount);

            row.innerHTML = `
                <div class="col-md-8">
                    <select name="involved_survey_teacher_ids[]" class="form-control teacher-select" required>
                        <option value="">-- Select Teacher --</option>
                        ${involvedSurveyTeachers.map(t => `<option value="${t.id}" ${t.id == teacherId ? 'selected' : ''}>
                            ${t.user.name}, ${t.designation.designation}, ${t.department.shortname}
                        </option>`).join('')}
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" name="involved_survey_student_amounts[]" class="form-control amount-input" placeholder="No of students" value="${amount}" required min="1">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-danger remove-row">🗑️</button>
                </div>
            `;

            container.appendChild(row);

            // Initialize Select2 for the newly added select input
            $(row).find('select').select2({
                theme: 'bootstrap',
                width: '100%',
                allowClear: true,
                placeholder: '-- Select Teacher --'
            });

            // Delete button logic: removes the current row when clicked
            row.querySelector('.remove-row').addEventListener('click', function () {
                row.remove();
            });
        }

        // Load pre-filled rows from DB if any data exists
        if (savedInvolvedSurveyAssign && savedInvolvedSurveyAssign.length > 0) {
            savedInvolvedSurveyAssign.forEach(assign => {
                createInvolvedSurveyRow(assign.teacher_id, assign.no_of_items);
            });
        }

        // Add new blank row
        document.getElementById('add-involved-survey-row').addEventListener('click', function () {
            createInvolvedSurveyRow();
        });

        // Form submission logic
        document.getElementById('form-list-of-involved-survey').addEventListener('submit', function (e) {
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

                // Validate teacher selection
                if (!teacherId) {
                    select.classList.add('is-invalid');
                    valid = false;
                }

                // Validate amount input
                if (!amount || amount <= 0) {
                    inputs[index].classList.add('is-invalid');
                    valid = false;
                }

                // Ensure teachers are not duplicated
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

            // Confirm submission
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

                            const submitBtn = document.getElementById('submit-list-of-involved-survey');
                            submitBtn.textContent = 'Update Involved Survey Committee';  // ✅ New label
                            submitBtn.classList.remove('btn-primary');
                            submitBtn.classList.add('btn-warning');

                            const cards = document.querySelectorAll('.card-list-of-involved-survey');
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
    </script>
@endpush

