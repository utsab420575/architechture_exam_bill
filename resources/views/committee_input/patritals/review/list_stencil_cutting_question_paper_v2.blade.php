@push('styles')
    <style>
        .card-list-of-stencil-cutting-question-paper {
            background-color: white;
            transition: background-color 0.6s ease-in-out;
        }

        .card-list-of-stencil-cutting-question-paper.fade-highlight {
            background-color: #28a745;
        }

        .card-list-of-stencil-cutting-question-paper.fade-out {
            background-color: white;
        }

        select.is-invalid, input.is-invalid {
            border-color: red;
        }
    </style>
@endpush

<form id="form-list-of-stencil-cutting-question-paper" action="{{ route('committee.input.review.stencil.cutting.committee.store') }}" method="POST">
    @csrf
    <input type="hidden" value="{{$sid}}" name="sid">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center">
                    <h2 class="card-title">
                        <span class="step-badge">12.a</span>
                        List of Stencill Cutting of Question paper (@ ****/- per stencil)</h2>
                </header>

                <div class="card-body card-list-of-stencil-cutting-question-paper">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="stencil_cutting_question_paper_rate">Per Stencil Rate</label>
                                <input type="number"  name="stencil_cutting_question_paper_rate" value="{{$stencill_cutting_per_stencil_rate??115}}" step="any" class="form-control" placeholder="Enter per student per thesis/project rate" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                        </div>
                        <div class="col-md-4 mb-4">
                        </div>
                    </div>

                    <div class="row mb-2 fw-bold mt-2">
                        <div class="col-md-8 text-start">Select Teacher</div>
                        <div class="col-md-3 text-start" style="margin-left:-15px;">No of Stencil</div>
                    </div>

                    {{--here will be add row--}}
                    <div id="dynamic-stencil-cutting-question-paper-container"></div>

                    <div class="mt-3 text-end">
                        <button type="button" id="add-stencil-cutting-question-paper-row" class="btn btn-sm btn-success me-2">+ Add Teacher</button>
                    </div>

                    <div class="text-end mt-3">
                        <button id="submit-list-of-stencil-cutting-question-paper" type="submit" class="btn btn-primary">
                            Submit Stencil Cutting Committee
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        let stencilCuttingRowCount = 0;
        const stencilCuttingStaffTeachers = @json($employees);
        const savedStencilCuttingCommittee = @json($savedRateAssignStencilCuttingCommittee);

        function createStencillCommitteeRow(teacherId = '', amount = '') {
            stencilCuttingRowCount++;

            const container = document.getElementById('dynamic-stencil-cutting-question-paper-container');
            const row = document.createElement('div');
            row.classList.add('row', 'align-items-center', 'mb-2');
            row.setAttribute('data-row', stencilCuttingRowCount);

            row.innerHTML = `
                <div class="row mb-3 align-items-center" data-row="${stencilCuttingRowCount}">
                    <!-- Teacher Select Column -->
                    <div class="col-md-8">
                        <select name="stencil_cutting_committee_teacher_ids[]" class="form-control teacher-select populate" data-row="${stencilCuttingRowCount}" required>
                            <option value="">-- Select Teacher --</option>
                            ${stencilCuttingStaffTeachers.map(t => `<option
                                value="${t.id}" ${t.id == teacherId ? 'selected' : ''}>
                                ${t.user.name}, ${t.designation.designation}, ${t.department.shortname}
                            </option>`).join('')}
                        </select>
                    </div>

                    <!-- Amount Column -->
                    <div class="col-md-3">
                        <input type="number" name="stencil_cutting_committee_amounts[]" step="any" class="form-control amount-input" placeholder="Provide Stencil Number" value="${amount}" required>
                    </div>

                    <!-- Delete Button Column -->
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-danger remove-row">🗑️</button>
                    </div>
                </div>
            `;

            container.appendChild(row);

            // Initialize Select2
            $(row).find('select').select2({
                theme: 'bootstrap',
                width: '100%',
                allowClear: true,
                placeholder: '-- Select Teacher --'
            });

            // Delete row logic
            row.querySelector('.remove-row').addEventListener('click', function () {
                row.remove();
            });
        }

        // Load pre-filled rows from DB
        if (savedStencilCuttingCommittee && savedStencilCuttingCommittee.length > 0) {
            savedStencilCuttingCommittee.forEach(assign => {
                createStencillCommitteeRow(assign.employee_id, assign.no_of_items);
            });
        }

        // Add blank new row
        document.getElementById('add-stencil-cutting-question-paper-row').addEventListener('click', function () {
            createStencillCommitteeRow();
        });

        // Submit logic
        document.getElementById('form-list-of-stencil-cutting-question-paper').addEventListener('submit', function (e) {
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
                            return response.json();
                        })
                        .then(data => {
                            Swal.fire('Success!', data.message, 'success');

                            const submitBtn = document.getElementById('submit-list-of-stencil-cutting-question-paper');
                            submitBtn.textContent = 'Update Stencil Cutting Committee';
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('btn-primary');
                            submitBtn.classList.add('btn-warning');

                            const cards = document.querySelectorAll('.card-list-of-stencil-cutting-question-paper');
                            cards.forEach(card => {
                                card.classList.add('fade-highlight');
                                setTimeout(() => card.classList.add('fade-out'), 1000);
                                setTimeout(() => card.classList.remove('fade-highlight', 'fade-out'), 1900);
                            });
                        })
                        .catch(error => {
                            Swal.fire('Error!', error.message || 'Something went wrong. Please try again.', 'error');
                        });
                }
            });
        });
    </script>
@endpush
