@push('styles')
    <style>
        .card-list-of-comparison-question-paper{background:white;transition:background-color .6s ease-in-out}
        .card-list-of-comparison-question-paper.fade-highlight{background:#28a745}
        .card-list-of-comparison-question-paper.fade-out{background:white}
        select.is-invalid, input.is-invalid{border-color:red}
    </style>
@endpush

<form id="form-list-of-comparison-question-paper" action="{{ route('committee.input.review.comparison.committee.store') }}" method="POST">
    @csrf
    <input type="hidden" value="{{ $sid }}" name="sid">

    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center">
                    <h2 class="card-title">
                        <span class="step-badge">11</span>
                        List of Comparison, Correction, Sketching & Distribution of Question Paper (@ ****/- per stencil)
                    </h2>
                </header>

                <div class="card-body card-list-of-comparison-question-paper">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="comparison-question-paper-rate">Per Question Rate</label>
                                <input type="number" name="comparison_question_paper_rate" id="comparison-question-paper-rate"
                                       value="{{ $comparison_rate ?? 1350 }}" step="any" class="form-control"
                                       placeholder="Enter per question rate" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4"></div>
                        <div class="col-md-4 mb-4"></div>
                    </div>

                    <div class="row mb-2 fw-bold mt-2">
                        <div class="col-md-8 text-start">Select Teacher(s)</div>
                        <div class="col-md-3 text-start" style="margin-left:-15px;">No. of Questions</div>
                    </div>

                    {{-- dynamic rows --}}
                    <div id="dynamic-comparison-question-paper-container"></div>

                    <div class="mt-3 text-end">
                        <button type="button" id="add-comparison-question-paper-row" class="btn btn-sm btn-success me-2">+ Add Teacher Group</button>
                    </div>

                    <div class="text-end mt-3">
                        <button id="submit-list-of-comparison-question-paper" type="submit" class="btn btn-primary">
                            Submit Comparison, Correction Committee
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        (function(){
            let compRowCount = 0;
            const comparisonTeachers = @json($teachers ?? []);
            const saved = @json($savedRateAssignComparisonCommittee ?? null);

            function createRow(selectedTeacherIds = [], questionCount = '') {
                compRowCount++;
                const container = document.getElementById('dynamic-comparison-question-paper-container');

                const row = document.createElement('div');
                row.className = 'row align-items-center mb-2';
                row.setAttribute('data-row-id', compRowCount);

                // Only Name – Dept
                let teacherOptions = '';
                comparisonTeachers.forEach(t => {
                    const isSel = selectedTeacherIds.includes(String(t.id)) ? 'selected' : '';
                    const name  = t?.user?.name ?? 'Unknown';
                    const dept  = t?.department?.shortname ? ` - ${t.department.shortname}` : '';
                    teacherOptions += `<option value="${t.id}" ${isSel}>${name}${dept}</option>`;
                });

                row.innerHTML = `
            <div class="col-md-8">
                <select name="comparison_question_committee_teacher_ids[${compRowCount}][]"
                        class="form-control comp-teacher-select" multiple required>
                    ${teacherOptions}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="comparison_question_committee_amounts[${compRowCount}]"
                       class="form-control comp-question-input" step="any"
                       placeholder="No. of questions" value="${questionCount}" required min="1">
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-danger comp-remove-row">🗑️</button>
            </div>
        `;

                container.appendChild(row);

                // Initialize Select2 if available
                if (window.$ && $.fn.select2) {
                    $(row).find('.comp-teacher-select').select2({
                        theme: 'bootstrap',
                        width: '100%',
                        placeholder: '-- Select Teacher(s) --',
                        allowClear: true,
                        closeOnSelect: false
                    });
                }

                // Remove row
                row.querySelector('.comp-remove-row')?.addEventListener('click', () => row.remove());
            }

            function loadSaved() {
                let loadedAny = false;
                if (!saved) return loadedAny;

                // Grouped format
                if (saved.full_grouped_data && saved.grouped_keys) {
                    saved.grouped_keys.forEach(g => {
                        const recs = saved.full_grouped_data[g] || [];
                        if (!recs.length) return;
                        const teacherIds = recs.map(r => String(r.teacher_id));
                        const questions  = recs[0]?.total_students || ''; // store group question total here
                        createRow(teacherIds, questions);
                        loadedAny = true;
                    });
                }
                // Flat array fallback
                else if (Array.isArray(saved)) {
                    const groups = {};
                    saved.forEach(r => {
                        const k = r.group_no ?? '1';
                        if (!groups[k]) groups[k] = [];
                        groups[k].push(r);
                    });
                    Object.values(groups).forEach(recs => {
                        const teacherIds = recs.map(r => String(r.teacher_id));
                        const questions  = recs[0]?.total_students || '';
                        createRow(teacherIds, questions);
                        loadedAny = true;
                    });
                }
                return loadedAny;
            }

            function validateForm() {
                const selects = document.querySelectorAll('.comp-teacher-select');
                const inputs  = document.querySelectorAll('.comp-question-input');
                let ok = true;

                selects.forEach((sel, i) => {
                    const selectedTeachers = Array.from(sel.selectedOptions).map(o => o.value);
                    const q = parseFloat(inputs[i]?.value ?? '0');

                    sel.classList.remove('is-invalid');
                    inputs[i]?.classList.remove('is-invalid');

                    if (!selectedTeachers.length) {
                        sel.classList.add('is-invalid'); ok = false;
                    }
                    if (!(q > 0)) {
                        inputs[i]?.classList.add('is-invalid'); ok = false;
                    }
                });

                return ok;
            }

            function submitForm(form) {
                const fd = new FormData(form);
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    body: fd
                })
                    .then(r => r.ok ? r.json() : r.json().then(e => { throw new Error(e.message || 'Server error') }))
                    .then(data => {
                        Swal.fire('Success!', data.message || 'Saved successfully', 'success');
                        const btn = document.getElementById('submit-list-of-comparison-question-paper');
                        btn.textContent = 'Update Comparison, Correction Committee';
                        btn.classList.replace('btn-primary', 'btn-warning');

                        document.querySelectorAll('.card-list-of-comparison-question-paper').forEach(card => {
                            card.classList.add('fade-highlight');
                            setTimeout(() => card.classList.add('fade-out'), 1000);
                            setTimeout(() => card.classList.remove('fade-highlight','fade-out'), 1900);
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error!', err.message || 'Something went wrong', 'error');
                    });
            }

            document.addEventListener('DOMContentLoaded', () => {
                const hadSaved = loadSaved();
                // If nothing loaded from DB, start with one blank row
                if (!hadSaved) createRow();

                document.getElementById('add-comparison-question-paper-row')
                    ?.addEventListener('click', () => createRow());

                document.getElementById('form-list-of-comparison-question-paper')
                    ?.addEventListener('submit', function(e){
                        e.preventDefault();
                        if (!validateForm()) {
                            Swal.fire('Validation Error', 'Please select teacher(s) and enter question counts for all rows.', 'error');
                            return;
                        }
                        Swal.fire({
                            title: 'Confirm Save',
                            text: 'Do you want to save the committee data?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Save!',
                            cancelButtonText: 'Cancel'
                        }).then(res => { if (res.isConfirmed) submitForm(this); });
                    });
            });
        })();
    </script>
@endpush

