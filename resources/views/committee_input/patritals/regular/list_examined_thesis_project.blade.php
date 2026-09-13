@push('styles')
    <style>
        .card-list-of-examined-thesis-project{background-color:#fff;transition:background-color .6s ease-in-out}
        .card-list-of-examined-thesis-project.fade-highlight{background-color:#28a745}
        .card-list-of-examined-thesis-project.fade-out{background-color:#fff}
        select.is-invalid, input.is-invalid{border-color:red}
    </style>
@endpush

<form id="form-list-of-examined-thesis-project" action="{{ route('committee.input.examined.thesis.project.store') }}" method="POST">
    @csrf
    <input type="hidden" name="sid" value="{{ $sid }}">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center">
                    <h2 class="card-title"><span class="step-badge">6.a</span> List of Teachers examined thesis/projects (@****/- thesis/projects)</h2>
                </header>

                <div class="card-body card-list-of-examined-thesis-project">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Per Student Per Result Rate</label>
                            <input type="number" name="examined_thesis_project_rate" step="any"
                                   value="{{ $examined_thesis_per_student_rate ?? 2700 }}"
                                   class="form-control" placeholder="Enter per student per result rate" required>
                        </div>
                        <div class="col-md-4 mb-4"></div>
                        <div class="col-md-4 mb-4"></div>
                    </div>

                    <div class="row mb-2 fw-bold">
                        <div class="col-md-4">Internal Teacher(s)</div>
                        <div class="col-md-6">External Teacher(s)</div>
                        <div class="col-md-1">Students</div>
                        <div class="col-md-1 text-end">Action</div>
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

        // data
        const allTeachersInternalExternal = @json($teachers);
        const saved = @json($savedRateAssignExaminedThesisProject);

        function optionHtml(teacher, selectedSet){
            const name = teacher?.user?.name ?? 'Unknown';
            const dept  = teacher?.department?.shortname ? ` ${teacher.department.shortname}` : '';
            const sel = selectedSet.has(String(teacher.id)) ? 'selected' : '';
            return `<option value="${teacher.id}" ${sel}>${name}-${dept}</option>`;
        }

        function createExaminedThesisProjectRow(internalIds = [], externalIds = [], studentCount = '') {
            examinedTheisProjectRowCount++;
            const rId = examinedTheisProjectRowCount;

            const internalSet = new Set((internalIds || []).map(String));
            const externalSet = new Set((externalIds || []).map(String));

            const container = document.getElementById('dynamic-examined-thesis-project-container');
            const row = document.createElement('div');
            row.className = 'row align-items-center mb-5';
            row.setAttribute('data-row-id', rId);

            let internalOptions = '';
            let externalOptions = '';
            allTeachersInternalExternal.forEach(t => {
                internalOptions += optionHtml(t, internalSet);
                externalOptions += optionHtml(t, externalSet);
            });

            row.innerHTML = `
            <div class="col-md-4">
                <select name="examined_internal_thesis_project_teacher_ids[${rId}][]"
                        class="form-control teacher-select internal-select"
                        multiple required>
                    ${internalOptions}
                </select>
            </div>
            <div class="col-md-6">
                <select name="examined_external_thesis_project_teacher_ids[${rId}][]"
                        class="form-control teacher-select external-select"
                        multiple required>
                    ${externalOptions}
                </select>
            </div>
            <div class="col-md-1">
                <input type="number"
                       name="examined_thesis_project_student_counts[${rId}]"
                       class="form-control student-count-input"
                       placeholder="No of students"
                       value="${studentCount ?? ''}"
                       required min="1">
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove">🗑️</button>
            </div>
        `;

            container.appendChild(row);

            // init select2
            if (window.$ && $.fn.select2) {
                $(row).find('.teacher-select').select2({
                    theme: 'bootstrap',
                    width: '100%',
                    placeholder: '-- Select Teacher(s) --',
                    allowClear: true,
                    closeOnSelect: false
                });
            }

            // remove row
            row.querySelector('.remove-row').addEventListener('click', () => row.remove());
        }

        // prefill (expects arrays)
        if (Array.isArray(saved) && saved.length > 0) {
            saved.forEach(assign => {
                // map keys if different
                const internalIds = assign.internal_teacher_ids ?? assign.internalIds ?? [];
                const externalIds = assign.external_teacher_ids ?? assign.externalIds ?? [];
                const studentCnt  = assign.student_count ?? assign.students ?? '';
                createExaminedThesisProjectRow(internalIds, externalIds, studentCnt);
            });
        } else {
            // start with one empty row
            createExaminedThesisProjectRow();
        }

        // add row
        document.getElementById('add-examined-thesis-project-row').addEventListener('click', () => {
            createExaminedThesisProjectRow();
        });

        // submit + validation
        document.getElementById('form-list-of-examined-thesis-project').addEventListener('submit', function(e){
            e.preventDefault();

            const form = this;
            const rows = form.querySelectorAll('[data-row-id]');
            let valid = true;

            rows.forEach(row => {
                const internalSel = row.querySelector('.internal-select');
                const externalSel = row.querySelector('.external-select');
                const studentInp  = row.querySelector('.student-count-input');

                // clear old states
                [internalSel, externalSel, studentInp].forEach(el => el.classList.remove('is-invalid'));

                // gather selections
                const internalVals = Array.from(internalSel.selectedOptions).map(o => o.value);
                const externalVals = Array.from(externalSel.selectedOptions).map(o => o.value);

                // must have at least one on each side
                if (internalVals.length === 0) { internalSel.classList.add('is-invalid'); valid = false; }
                if (externalVals.length === 0) { externalSel.classList.add('is-invalid'); valid = false; }

                // no overlap within the SAME row
                const overlap = internalVals.some(v => externalVals.includes(v));
                if (overlap) { internalSel.classList.add('is-invalid'); externalSel.classList.add('is-invalid'); valid = false; }

                // student count > 0
                const cnt = parseInt(studentInp.value, 10);
                if (isNaN(cnt) || cnt <= 0) { studentInp.classList.add('is-invalid'); valid = false; }
            });

            if (!valid) {
                Swal.fire('Validation Failed',
                    'Per row: choose at least one Internal teacher and one External teacher (with no overlap), and set Students > 0.',
                    'error');
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to save the committee data?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: formData
                })
                    .then(res => {
                        if (!res.ok) return res.json().then(err => { throw new Error(err.message || 'Unknown error'); });
                        return res.json();
                    })
                    .then(data => {
                        Swal.fire('Success!', data.message || 'Saved successfully.', 'success');
                        const btn = document.getElementById('submit-list-of-examined-thesis-project');
                        btn.textContent = 'Update Examined Thesis Project Committee';
                        btn.classList.remove('btn-primary'); btn.classList.add('btn-warning');

                        document.querySelectorAll('.card-list-of-examined-thesis-project').forEach(card => {
                            card.classList.add('fade-highlight');
                            setTimeout(() => card.classList.add('fade-out'), 1000);
                            setTimeout(() => card.classList.remove('fade-highlight', 'fade-out'), 1900);
                        });
                    })
                    .catch(err => Swal.fire('Error!', err.message || 'Something went wrong. Please try again.', 'error'));
            });
        });
    </script>
@endpush
