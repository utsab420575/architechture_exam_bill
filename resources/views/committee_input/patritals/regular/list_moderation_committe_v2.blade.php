@push('styles')
    <style>
        .card-list-of-moderation-committee {
            background-color: white;
            transition: background-color 0.6s ease-in-out;
        }

        .card-list-of-moderation-committee.fade-highlight {
            background-color: #28a745;
        }

        .card-list-of-moderation-committee.fade-out {
            background-color: white;
        }

        select.is-invalid, input.is-invalid {
            border-color: red;
        }
    </style>
@endpush

<form id="form-list-of-moderation-committee" action="{{ route('committee.input.regular.examination.moderation.committee.store') }}" method="POST">
    @csrf
    <input type="hidden" id="{{$sid}}" name="sid" value="{{$sid}}">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header">
                    <h2 class="card-title">List of Examination Committee/Moderation Committee Members @ min***</h2>
                </header>

                <div class="card-body card-list-of-moderation-committee">
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_week">Min rate per member</label>
                                <input type="number"  name="moderation_committee_min_rate" value="1500" step="any" class="form-control" placeholder="Min rate per member" required="">
                            </div>
                        </div>
                        <div class="col-md-4"></div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_week">Max rate per member:</label>
                                <input type="number"  name="moderation_committee_max_rate" value="5000" step="any" class="form-control" placeholder="Max rate per member" required="">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2 fw-bold">
                        <div class="col-md-1 text-center">Select</div>
                        <div class="col-md-6">Teacher</div>
                        <div class="col-md-4">Amount(Taka)</div>
                    </div>

                    {{--here will be add checkbox--}}
                    <div id="dynamic-moderation-container"></div>

                    <div class="mt-3 text-end">
                        <button type="button" id="add-moderation-committee-row" class="btn btn-sm btn-success me-2">+ Add Teacher</button>
                        <button type="button" id="remove-moderation-committee-row" class="btn btn-sm btn-danger">- Remove Last</button>
                    </div>

                    <div class="text-end mt-3">
                        <button id="submit-list-of-moderation-committee" type="submit" class="btn btn-primary">
                            Submit Moderation Committee
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>

@push('scripts')
        <script>
            let moderationCommitteeRowCount = 0;
            const moderationCommitteeTeachers = @json($teachers); // Full teacher list
            const savedModerationAssigns = @json($savedModerationAssigns); // Pre-saved entries from DB

            function createTeacherRow(teacherId = '', amount = '', isChecked = false) {
                moderationCommitteeRowCount++;

                const container = document.getElementById('dynamic-moderation-container');
                const row = document.createElement('div');
                row.classList.add('row', 'align-items-center', 'mb-2');
                row.setAttribute('data-row', moderationCommitteeRowCount);

                row.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-1 text-center">
                    <input type="checkbox" class="form-check-input moderation-committee-toggle-input mt-2"
                           data-row="${moderationCommitteeRowCount}" ${isChecked ? 'checked' : ''}>
                </div>
                <div class="col-md-6">
                    <select name="moderation_committee_teacher_ids[]" data-plugin-selectTwo
                            class="form-control teacher-select populate"
                            data-row="${moderationCommitteeRowCount}" ${isChecked ? '' : 'disabled'} required>
                        <option value="">-- Select Teacher --</option>
                        ${moderationCommitteeTeachers.map(t => `
                            <option value="${t.id}" ${t.id == teacherId ? 'selected' : ''}>
                                ${t.user.name}, ${t.designation.designation}, ${t.department.shortname}
                            </option>
                        `).join('')}
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" name="moderation_committee_amounts[]"
                           class="form-control amount-input"
                           placeholder="Provide Amount"
                           value="${amount}"
                           ${isChecked ? '' : 'disabled'} required>
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

                // Checkbox toggle logic
                const checkbox = row.querySelector('.moderation-committee-toggle-input');
                checkbox.addEventListener('change', function () {
                    const rowIndex = this.getAttribute('data-row');
                    const select = row.querySelector(`.teacher-select[data-row="${rowIndex}"]`);
                    const input = row.querySelector('.amount-input');

                    select.disabled = !this.checked;
                    input.disabled = !this.checked;

                    if (!this.checked) {
                        select.value = '';
                        input.value = '';
                        select.classList.remove('is-invalid');
                        input.classList.remove('is-invalid');
                        $(select).trigger('change');
                    }
                });
            }

            // Load saved moderation assigns (if any)
            if (savedModerationAssigns && savedModerationAssigns.length > 0) {
                savedModerationAssigns.forEach(assign => {
                    createTeacherRow(assign.teacher_id, assign.total_amount, true);
                });
            }

            // Add new blank row
            document.getElementById('add-moderation-committee-row').addEventListener('click', function () {
                createTeacherRow();
            });

            // Remove last row
            document.getElementById('remove-moderation-committee-row').addEventListener('click', function () {
                const container = document.getElementById('dynamic-moderation-container');
                if (container.lastElementChild) {
                    container.removeChild(container.lastElementChild);
                    moderationCommitteeRowCount--;
                }
            });

            // Handle form submission
            document.getElementById('form-list-of-moderation-committee').addEventListener('submit', function (e) {
                e.preventDefault();

                const form = this;
                const checkedRows = form.querySelectorAll('.moderation-committee-toggle-input:checked');

                if (checkedRows.length === 0) {
                    Swal.fire('No Teachers Selected', 'Please select at least one teacher and fill all required fields.', 'warning');
                    return;
                }

                let valid = true;
                let teacherIds = [];

                checkedRows.forEach(checkbox => {
                    const row = checkbox.closest('.row');
                    const select = row.querySelector('.teacher-select');
                    const input = row.querySelector('.amount-input');

                    select.classList.remove('is-invalid');
                    input.classList.remove('is-invalid');

                    const teacherId = select.value;
                    const amount = input.value;

                    if (!teacherId) {
                        select.classList.add('is-invalid');
                        valid = false;
                    }

                    if (!amount || amount <= 0) {
                        input.classList.add('is-invalid');
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

                                const submitBtn = document.getElementById('submit-list-of-moderation-committee');
                                submitBtn.textContent = 'Already Saved';
                                submitBtn.disabled = true;
                                submitBtn.classList.remove('btn-primary');
                                submitBtn.classList.add('btn-success');

                                const cards = document.querySelectorAll('.card-list-of-moderation-committee');
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
