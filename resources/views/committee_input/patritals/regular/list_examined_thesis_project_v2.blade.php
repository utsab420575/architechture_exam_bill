@push('styles')
    <style>
        .card-list-of-examined-thesis-project {
            background-color: white;
            transition: background-color 0.6s ease-in-out;
        }

        .card-list-of-examined-thesis-project.fade-highlight {
            background-color: #28a745;
        }

        .card-list-of-examined-thesis-project.fade-out {
            background-color: white;
        }

        select.is-invalid, input.is-invalid {
            border-color: red;
        }
    </style>
@endpush

<form id="form-list-of-examined-thesis-project" action="{{ route('committee.input.examined.thesis.project.store') }}" method="POST">
    @csrf
    <input type="hidden" name="sid" value="{{$sid}}">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header">
                    <h2 class="card-title">List of Teachers examined thesis/projects (@****/- thesis/projects)</h2>
                </header>

                <div class="card-body card-list-of-examined-thesis-project">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="examined_thesis_project_rate">Per Student Per Result Rate</label>
                                <input type="number"  name="examined_thesis_project_rate" step="any" value="2700" class="form-control" placeholder="Enter per student per result rate" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                        </div>
                        <div class="col-md-4 mb-4">
                        </div>
                    </div>


                    <div class="row mb-2 fw-bold">
                        <div class="col-md-7">Teacher</div>
                        <div class="col-md-2">No of Students (Internal)</div>
                        <div class="col-md-2">No of Students (External)</div>
                    </div>

                    <div id="dynamic-examined-thesis-project-container"></div>

                    <div class="mt-3 text-end">
                        <button type="button" id="add-examined-thesis-project-row" class="btn btn-sm btn-success me-2">+ Add Teacher</button>
                    </div>

                    <div class="text-end mt-3">
                        <button id="submit-list-of-examined-thesis-project" type="submit" class="btn btn-primary">
                            Submit Examined Thesis Project Committee
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        let examinedTheisProjectRowCount = 0;
        const examinedThesisProjectTeachers = @json($teachers);
        const savedExaminedThesisProjectAssign = @json($savedRateAssignExaminedThesisProject);

        function createTeacherRow() {
            examinedTheisProjectRowCount++;

            const container = document.getElementById('dynamic-examined-thesis-project-container');
            const row = document.createElement('div');
            row.classList.add('row', 'align-items-center', 'mb-2');
            row.setAttribute('data-row', examinedTheisProjectRowCount);

            row.innerHTML = `

                <div class="col-md-7">
                    <select name="examined_thesis_project_teacher_ids[]" data-plugin-selectTwo class="form-control teacher-select populate" data-row="${examinedTheisProjectRowCount}" disabled required>
                        <option value="">-- Select Teacher --</option>
                        ${examinedThesisProjectTeachers.map(t => `<option value="${t.id}">${t.user.name}, ${t.designation.designation},${t.department.shortname}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="examined_internal_thesis_project_student_amounts[]" class="form-control internal-input" placeholder="Internal students" disabled  min="0">
                </div>
                <div class="col-md-2">
                    <input type="number" name="examined_external_thesis_project_student_amounts[]" class="form-control external-input" placeholder="External students" disabled  min="0">
                </div>
            `;

            container.appendChild(row);
            //2nd change:
            // Re-initialize Select2 for the new element
            $(row).find('select').select2({
                theme: 'bootstrap',
                width: '100%',
                allowClear: true,
                placeholder: '-- Select Teacher --'
            });

            const checkbox = row.querySelector('.examined-thesis-project-toggle-input');
            const select = row.querySelector('.teacher-select');
            const internalInput = row.querySelector('.internal-input');
            const externalInput = row.querySelector('.external-input');

            checkbox.addEventListener('change', function () {
                const isChecked = this.checked;

                select.disabled = !isChecked;
                internalInput.disabled = !isChecked;
                externalInput.disabled = !isChecked;

                if (!isChecked) {
                    select.value = '';
                    internalInput.value = '';
                    externalInput.value = '';
                    select.classList.remove('is-invalid');
                    internalInput.classList.remove('is-invalid');
                    externalInput.classList.remove('is-invalid');
                }
            });
        }

        document.getElementById('add-examined-thesis-project-row').addEventListener('click', createTeacherRow);

        document.getElementById('remove-examined-thesis-project-row').addEventListener('click', function () {
            const container = document.getElementById('dynamic-examined-thesis-project-container');
            if (container.lastElementChild) {
                container.removeChild(container.lastElementChild);
                examinedTheisProjectRowCount--;
            }
        });

        document.getElementById('form-list-of-examined-thesis-project').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
            const checkedRows = form.querySelectorAll('.examined-thesis-project-toggle-input:checked');

            if (checkedRows.length === 0) {
                Swal.fire('No Teachers Selected', 'Please select at least one teacher and fill all required fields.', 'warning');
                return;
            }

            let valid = true;
            let teacherIds = [];

            checkedRows.forEach(checkbox => {
                const row = checkbox.closest('.row');
                const select = row.querySelector('.teacher-select');
                const internalInput = row.querySelector('.internal-input');
                const externalInput = row.querySelector('.external-input');

                select.classList.remove('is-invalid');
                internalInput.classList.remove('is-invalid');
                externalInput.classList.remove('is-invalid');

                const teacherId = select.value;
                const internalValue = parseInt(internalInput.value) || 0;
                const externalValue = parseInt(externalInput.value) || 0;

                if (!teacherId) {
                    select.classList.add('is-invalid');
                    valid = false;
                }

                if (internalValue < 1 && externalValue < 1) {
                    internalInput.classList.add('is-invalid');
                    externalInput.classList.add('is-invalid');
                    valid = false;
                }

                if (teacherIds.includes(teacherId)) {
                    select.classList.add('is-invalid');
                    valid = false;
                }

                teacherIds.push(teacherId);
            });

            if (!valid) {
                Swal.fire('Validation Failed', 'Each selected row must include a unique teacher and either internal or external student count.', 'error');
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
                                // Return the error JSON and throw it
                                return response.json().then(err => {
                                    throw new Error(err.message || 'Unknown error occurred.');
                                });
                            }
                            return response.json(); // if response is OK
                        })
                        .then(data => {
                            console.log("Server response:", data); // Debug log
                            Swal.fire('Success!', data.message, 'success');

                            const submitBtn = document.getElementById('submit-list-of-examined-thesis-project');
                            submitBtn.textContent = 'Update Examined Thesis Project Committee';  // ✅ New label
                            submitBtn.classList.remove('btn-primary');
                            submitBtn.classList.add('btn-warning');

                            const cards = document.querySelectorAll('.card-list-of-examined-thesis-project');
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
