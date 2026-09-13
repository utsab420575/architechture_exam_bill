@push('styles')
    <style>
        .card-list-of-printing-question-paper {
            background-color: white;
            transition: background-color 0.6s ease-in-out;
        }
        .card-list-of-printing-question-paper.fade-highlight { background-color: #28a745; }
        .card-list-of-printing-question-paper.fade-out { background-color: white; }
        select.is-invalid, input.is-invalid { border-color: red; }
    </style>
@endpush

<form id="form-list-of-printing-question-paper" action="{{ route('committee.input.review.printing.question.committee.store') }}" method="POST">
    @csrf
    <input type="hidden" value="{{ $sid }}" name="sid">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header">
                    <h2 class="card-title d-flex align-items-center">
                        <span class="step-badge">12.b</span>
                        List of Printing of Question paper (@ ****/- per stencil)
                    </h2>
                </header>

                <div class="card-body card-list-of-printing-question-paper">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="printing_question_paper_rate">Per Stencil Rate</label>
                                <input
                                    type="number"
                                    name="printing_question_paper_rate"
                                    id="printing-question-paper-rate"
                                    value="{{ $print_question_paper_rate ?? 35 }}"
                                    step="any"
                                    class="form-control"
                                    placeholder="Enter per stencil rate"
                                    required
                                >
                            </div>
                        </div>
                        <div class="col-md-4 mb-4"></div>
                        <div class="col-md-4 mb-4"></div>
                    </div>

                    <div class="row mb-2 fw-bold mt-2">
                        <div class="col-md-8 text-start">Select Teacher(s)</div>
                        <div class="col-md-3 text-start" style="margin-left:-15px;">No of Stencils</div>
                    </div>

                    {{-- dynamic rows go here --}}
                    <div id="dynamic-printing-question-paper-container"></div>

                    <div class="mt-3 text-end">
                        <button type="button" id="add-printing-question-paper-row" class="btn btn-sm btn-success me-2">+ Add Row</button>
                    </div>

                    <div class="text-end mt-3">
                        <button id="submit-list-of-printing-question-paper" type="submit" class="btn btn-primary">
                            Submit Printing Question Committee
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        // Global variables
        let rowCount = 0;
        const allTeachers = @json($teachers ?? []);
        const savedData   = @json($savedRateAssignPrintingQuestion ?? null);

        // Debug: Show data in console
        console.log('Teachers:', allTeachers);
        console.log('Saved Data (Printing Question):', savedData);

        /**
         * Helper: build label "Name-Dept"
         */
        function optionLabel(t) {
            const name = t?.user?.name || 'Unknown';
            const dept = t?.department?.shortname ? `-${t.department.shortname}` : '';
            return `${name}${dept}`;
        }

        /**
         * Create a new row: multi-teacher select + stencil count
         */
        function createRow(selectedTeacherIds = [], stencilAmount = '') {
            rowCount++;
            const container = document.getElementById('dynamic-printing-question-paper-container');

            // Build teacher options HTML
            let teacherOptions = '';
            allTeachers.forEach(teacher => {
                const isSelected = selectedTeacherIds.includes(String(teacher.id)) ? 'selected' : '';
                teacherOptions += `<option value="${teacher.id}" ${isSelected}>${optionLabel(teacher)}</option>`;
            });

            // Row HTML
            const row = document.createElement('div');
            row.className = 'row align-items-center mb-2';
            row.setAttribute('data-row-id', rowCount);
            row.innerHTML = `
            <div class="col-md-8">
                <select name="print_question_committee_teacher_ids[${rowCount}][]"
                        class="form-control teacher-select-printing-question"
                        multiple required>
                    ${teacherOptions}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number"
                       name="printing_question_committee_amounts[${rowCount}]"
                       class="form-control"
                       placeholder="No of stencils"
                       value="${stencilAmount}"
                       required min="1" step="any">
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-btn">🗑️</button>
            </div>
        `;

            // Add to DOM
            container.appendChild(row);

            // Init Select2 (if available)
            if (window.$ && $.fn.select2) {
                $(row).find('.teacher-select-printing-question').select2({
                    theme: 'bootstrap',
                    width: '100%',
                    placeholder: '-- Select Teacher(s) --',
                    allowClear: true,
                    closeOnSelect: false
                });
            }

            // Remove handler
            row.querySelector('.remove-btn').addEventListener('click', function () {
                row.remove();
            });

            console.log(`Created row ${rowCount} (Printing):`, selectedTeacherIds, 'stencils:', stencilAmount);
        }

        /**
         * Load saved data (supports grouped-object format from getTeacherWithGroup)
         */
        function loadSavedData() {
            if (!savedData) {
                console.log('No saved data found (Printing)');
                return;
            }

            console.log('Loading saved data (Printing)...');

            // Grouped-object format: { grouped_keys: [...], full_grouped_data: {key: [records]} }
            if (savedData.full_grouped_data && savedData.grouped_keys) {
                console.log('Using grouped data format (Printing)');

                savedData.grouped_keys.forEach(groupKey => {
                    const groupRecords = savedData.full_grouped_data[groupKey] || [];
                    if (groupRecords.length === 0) return;

                    // Accept teacher_id OR employee_id (depending on how you saved)
                    const teacherIds = groupRecords.map(r => String(r.teacher_id ?? r.employee_id)).filter(Boolean);

                    // Prefer original row amount if available (total_students); fallback to no_of_items
                    const stencilAmount = groupRecords[0].total_students ?? groupRecords[0].no_of_items ?? '';

                    createRow(teacherIds, stencilAmount);

                    console.log(`Loaded group ${groupKey} (Printing):`, teacherIds, 'stencils:', stencilAmount);
                });
            }
            // Flat array fallback
            else if (Array.isArray(savedData)) {
                console.log('Using flat array format (Printing)');

                const groups = {};
                savedData.forEach(record => {
                    const key = (record.group_no !== undefined && record.group_no !== null)
                        ? `G_${record.group_no}`
                        : `A_${record.total_students ?? record.no_of_items ?? 'default'}`;
                    if (!groups[key]) groups[key] = [];
                    groups[key].push(record);
                });

                Object.values(groups).forEach(groupRecords => {
                    const teacherIds = groupRecords.map(r => String(r.teacher_id ?? r.employee_id)).filter(Boolean);
                    const stencilAmount = groupRecords[0].total_students ?? groupRecords[0].no_of_items ?? '';
                    createRow(teacherIds, stencilAmount);
                });
            }
        }

        /**
         * Validate before submit
         */
        function validateForm() {
            const teacherSelects = document.querySelectorAll('.teacher-select-printing-question');
            const stencilInputs  = document.querySelectorAll('input[name^="printing_question_committee_amounts"]');
            let isValid = true;

            console.log("🔍 Printing validation start. Rows:", teacherSelects.length);

            teacherSelects.forEach((select, i) => {
                const selectedTeachers = Array.from(select.selectedOptions).map(o => o.value);
                const stencilAmount    = stencilInputs[i]?.value || '';

                // Reset errors
                select.classList.remove('is-invalid');
                stencilInputs[i]?.classList.remove('is-invalid');

                // Require teachers
                if (selectedTeachers.length === 0) {
                    select.classList.add('is-invalid');
                    isValid = false;
                }

                // Require positive stencil count
                if (!stencilAmount || Number(stencilAmount) <= 0) {
                    stencilInputs[i]?.classList.add('is-invalid');
                    isValid = false;
                }

                console.log(`Row ${i + 1} validation (Printing):`, { selectedTeachers, stencilAmount });
            });

            console.log("✅ Printing validation result:", isValid);
            return isValid;
        }

        /**
         * Submit via AJAX
         */
        function submitForm(form) {
            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // (Optional) Debug: log outgoing FormData
            console.log("🚀 Submitting (Printing) FormData:");
            for (const [k, v] of formData.entries()) console.log(`  ${k} =`, v);

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            })
                .then(response => {
                    if (response.ok) return response.json();
                    return response.json().then(err => { throw new Error(err.message || 'Server error'); });
                })
                .then(data => {
                    Swal.fire('Success!', data.message || 'Data saved successfully', 'success');

                    const submitBtn = document.getElementById('submit-list-of-printing-question-paper');
                    submitBtn.textContent = 'Update Printing Question Committee';
                    submitBtn.classList.remove('btn-primary');
                    submitBtn.classList.add('btn-warning');

                    document.querySelectorAll('.card-list-of-printing-question-paper').forEach(card => {
                        card.classList.add('fade-highlight');
                        setTimeout(() => card.classList.add('fade-out'), 1000);
                        setTimeout(() => card.classList.remove('fade-highlight', 'fade-out'), 1900);
                    });
                })
                .catch(error => {
                    console.error('Error (Printing):', error);
                    Swal.fire('Error!', error.message || 'Something went wrong', 'error');
                });
        }

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', function () {
            // Prefill from DB
            loadSavedData();

            // Add row button
            document.getElementById('add-printing-question-paper-row').addEventListener('click', function () {
                createRow(); // empty row
            });

            // Submit handler
            document.getElementById('form-list-of-printing-question-paper').addEventListener('submit', function (e) {
                e.preventDefault();

                if (!validateForm()) {
                    Swal.fire('Validation Error', 'Please select teacher(s) and enter stencil numbers for all rows', 'error');
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
    </script>
@endpush

