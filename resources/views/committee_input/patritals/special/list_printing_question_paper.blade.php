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

<form id="form-list-of-printing-question-paper" action="{{ route('committee.input.special.printing.question.committee.store') }}" method="POST">
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
                                <label for="printing-question-paper-rate">Per Stencil Rate</label>
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
                        <button type="button" id="add-printing-question-paper-row" class="btn btn-sm btn-success me-2">+ Add Employee</button>
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
        (function () {
            // ===== Local scope (no globals) =====
            let rowCount = 0;
            const staff = @json($employees ?? []);
            const saved = @json($savedRateAssignPrintingQuestion ?? null);

            const container = document.getElementById('dynamic-printing-question-paper-container');

            // Helper: "Name-Dept"
            const optionLabel = (t) => {
                const n = t?.user?.name || 'Unknown';
                const d = t?.department?.shortname ? `-${t.department.shortname}` : '';
                return `${n}${d}`;
            };

            /**
             * Create row: multi-teacher select + stencil amount
             * Names mirror your pattern:
             *   - print_question_committee_teacher_ids[ROW][]
             *   - printing_question_committee_amounts[ROW]
             */
            function createRow(selectedTeacherIds = [], stencilAmount = '') {
                rowCount++;

                const opts = staff.map(t => {
                    const id = String(t.id);
                    const sel = selectedTeacherIds.includes(id) ? 'selected' : '';
                    return `<option value="${id}" ${sel}>${optionLabel(t)}</option>`;
                }).join('');

                const row = document.createElement('div');
                row.className = 'row align-items-center mb-2 printing-row';
                row.setAttribute('data-row-id', rowCount);
                row.innerHTML = `
            <div class="col-md-8">
                <select name="print_question_committee_teacher_ids[${rowCount}][]"
                        class="form-control teacher-select-printing-question"
                        multiple required>
                    ${opts}
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

                container.appendChild(row);

                // Select2 (optional)
                if (window.$ && $.fn.select2) {
                    $(row).find('.teacher-select-printing-question').select2({
                        theme: 'bootstrap',
                        width: '100%',
                        placeholder: '-- Select Teacher(s) --',
                        allowClear: true,
                        closeOnSelect: false
                    });
                }

                row.querySelector('.remove-btn').addEventListener('click', () => row.remove());
            }

            /**
             * Load saved data (grouped-object or flat array)
             */
            function loadSavedData() {
                if (!saved) return;

                // grouped shape: { grouped_keys: [...], full_grouped_data: { key: [records...] } }
                if (saved.full_grouped_data && saved.grouped_keys) {
                    saved.grouped_keys.forEach(key => {
                        const recs = saved.full_grouped_data[key] || [];
                        if (!recs.length) return;

                        const ids = recs.map(r => String(r.teacher_id ?? r.employee_id)).filter(Boolean);
                        const amt = recs[0].total_students ?? recs[0].no_of_items ?? '';
                        createRow(ids, amt);
                    });
                }
                // flat shape
                else if (Array.isArray(saved)) {
                    const groups = {};
                    saved.forEach(r => {
                        const k = (r.group_no !== undefined && r.group_no !== null)
                            ? `G_${r.group_no}`
                            : `A_${r.total_students ?? r.no_of_items ?? 'default'}`;
                        (groups[k] ??= []).push(r);
                    });

                    Object.values(groups).forEach(recs => {
                        const ids = recs.map(r => String(r.teacher_id ?? r.employee_id)).filter(Boolean);
                        const amt = recs[0].total_students ?? recs[0].no_of_items ?? '';
                        createRow(ids, amt);
                    });
                }
            }

            /**
             * Index-based validation (like your oral blade)
             */
            function validateForm() {
                const teacherSelects = document.querySelectorAll('.teacher-select-printing-question');
                const amountInputs   = document.querySelectorAll('input[name^="printing_question_committee_amounts"]');
                let ok = true;

                teacherSelects.forEach((select, i) => {
                    const selected = Array.from(select.selectedOptions).map(o => o.value);
                    const amount   = amountInputs[i]?.value || '';

                    select.classList.remove('is-invalid');
                    amountInputs[i]?.classList.remove('is-invalid');

                    if (!selected.length) {
                        select.classList.add('is-invalid');
                        ok = false;
                    }
                    if (!amount || Number(amount) <= 0) {
                        amountInputs[i]?.classList.add('is-invalid');
                        ok = false;
                    }
                });

                return ok;
            }

            /**
             * Submit via AJAX
             */
            function submitForm(form) {
                const fd   = new FormData(form);
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    body: fd
                })
                    .then(async (r) => {
                        let payload = null;
                        try { payload = await r.clone().json(); } catch (_) {}
                        if (!r.ok) throw new Error(payload?.message || `Server error (${r.status})`);
                        return payload;
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
                    .catch(err => {
                        console.error('[Printing] error:', err);
                        Swal.fire('Error!', err.message || 'Something went wrong', 'error');
                    });
            }

            // ===== Init =====
            document.addEventListener('DOMContentLoaded', () => {
                loadSavedData();

                document.getElementById('add-printing-question-paper-row')
                    .addEventListener('click', () => createRow());

                document.getElementById('form-list-of-printing-question-paper')
                    .addEventListener('submit', function (e) {
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
                        }).then((res) => {
                            if (res.isConfirmed) submitForm(this);
                        });
                    });
            });
        })();
    </script>
@endpush
