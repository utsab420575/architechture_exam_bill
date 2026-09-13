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

<form id="form-list-of-stencil-cutting-question-paper" action="{{ route('committee.input.special.stencil.cutting.committee.store') }}" method="POST">
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


                    <hr>
                    <div class="row mb-2 fw-bold mt-2">
                        <div class="col-md-8 text-start">Select Teacher</div>
                        <div class="col-md-3 text-start" style="margin-left:-15px;">No of Stencil</div>
                    </div>
                    {{--<div class="row mb-2 fw-bold">
                        <div class="col-md-1 text-center">Select</div>
                        <div class="col-md-6">Name</div>
                        <div class="col-md-4">No of Stencil</div>
                    </div>--}}

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
        (function () {
            // ===== same pattern as oral_examination =====
            let rowCount = 0;
            const allTeachers = @json($teachers ?? []);
            const savedData   = @json($savedRateAssignStencilCuttingCommittee ?? null);

            const container = document.getElementById('dynamic-stencil-cutting-question-paper-container');

            const label = (t) => {
                const name = t?.user?.name || 'Unknown';
                const dept = t?.department?.shortname ? `-${t.department.shortname}` : '';
                return `${name}${dept}`;
            };

            /**
             * Create one row: MULTI teacher select + ONE stencil amount
             * Field names mirror oral_examination style but with stencil prefixes.
             */
            function createRow(selectedTeacherIds = [], stencilAmount = '') {
                rowCount++;

                const options = allTeachers.map(t => {
                    const id = String(t.id);
                    const sel = selectedTeacherIds.includes(id) ? 'selected' : '';
                    return `<option value="${id}" ${sel}>${label(t)}</option>`;
                }).join('');

                const row = document.createElement('div');
                row.className = 'row align-items-center mb-2';
                row.setAttribute('data-row-id', rowCount);
                row.innerHTML = `
            <div class="col-md-8">
                <select
                    name="stencil_cutting_committee_teacher_ids[${rowCount}][]"
                    class="form-control teacher-select-stencil-cutting"
                    multiple required>
                    ${options}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number"
                       name="stencil_cutting_committee_amounts[${rowCount}]"
                       class="form-control"
                       placeholder="No of stencils"
                       value="${stencilAmount}"
                       required min="1" step="any">
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-row">🗑️</button>
            </div>
        `;

                container.appendChild(row);

                if (window.$ && $.fn.select2) {
                    $(row).find('.teacher-select-stencil-cutting').select2({
                        theme: 'bootstrap',
                        width: '100%',
                        placeholder: '-- Select Teacher(s) --',
                        allowClear: true,
                        closeOnSelect: false
                    });
                }

                row.querySelector('.remove-row').addEventListener('click', () => row.remove());
            }

            /**
             * === loadSavedData (index-based) — like oral_examination ===
             * Supports grouped & flat shapes.
             */
            function loadSavedData() {
                if (!savedData) return;

                if (savedData.full_grouped_data && savedData.grouped_keys) {
                    savedData.grouped_keys.forEach(groupKey => {
                        const groupRecords = savedData.full_grouped_data[groupKey] || [];
                        if (groupRecords.length === 0) return;

                        const teacherIds = groupRecords
                            .map(record => String(record.teacher_id ?? record.employee_id))
                            .filter(Boolean);

                        const amount = groupRecords[0].total_students ?? groupRecords[0].no_of_items ?? '';
                        createRow(teacherIds, amount);
                    });
                } else if (Array.isArray(savedData)) {
                    const groups = {};
                    savedData.forEach(record => {
                        const key = (record.group_no !== undefined && record.group_no !== null)
                            ? `G_${record.group_no}`
                            : `A_${record.total_students ?? record.no_of_items ?? 'default'}`;
                        (groups[key] ??= []).push(record);
                    });

                    Object.values(groups).forEach(groupRecords => {
                        const teacherIds = groupRecords
                            .map(record => String(record.teacher_id ?? record.employee_id))
                            .filter(Boolean);
                        const amount = groupRecords[0].total_students ?? groupRecords[0].no_of_items ?? '';
                        createRow(teacherIds, amount);
                    });
                }
            }

            /**
             * === validateForm (index-based) — like oral_examination ===
             */
            function validateForm() {
                const teacherSelects = document.querySelectorAll('.teacher-select-stencil-cutting');
                const amountInputs   = document.querySelectorAll('input[name^="stencil_cutting_committee_amounts"]');
                let isValid = true;

                teacherSelects.forEach((select, index) => {
                    const selectedTeachers = Array.from(select.selectedOptions).map(opt => opt.value);
                    const amount = amountInputs[index]?.value || '';

                    select.classList.remove('is-invalid');
                    amountInputs[index]?.classList.remove('is-invalid');

                    if (selectedTeachers.length === 0) {
                        select.classList.add('is-invalid');
                        isValid = false;
                    }
                    if (!amount || Number(amount) <= 0) {
                        amountInputs[index]?.classList.add('is-invalid');
                        isValid = false;
                    }
                    // Optional: block duplicates within the same row
                    if (new Set(selectedTeachers).size !== selectedTeachers.length) {
                        select.classList.add('is-invalid');
                        isValid = false;
                    }
                });

                return isValid;
            }

            /**
             * Submit via AJAX (same UX as oral_examination)
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
                        if (window.Swal && Swal.fire) {
                            Swal.fire('Success!', data.message || 'Data saved successfully', 'success');
                        }
                        const submitBtn = document.getElementById('submit-list-of-stencil-cutting-question-paper');
                        submitBtn.textContent = 'Update Stencil Cutting Committee';
                        submitBtn.classList.remove('btn-primary');
                        submitBtn.classList.add('btn-warning');

                        document.querySelectorAll('.card-list-of-stencil-cutting-question-paper').forEach(card => {
                            card.classList.add('fade-highlight');
                            setTimeout(() => card.classList.add('fade-out'), 1000);
                            setTimeout(() => card.classList.remove('fade-highlight', 'fade-out'), 1900);
                        });
                    })
                    .catch(err => {
                        if (window.Swal && Swal.fire) {
                            Swal.fire('Error!', err.message || 'Something went wrong. Please try again.', 'error');
                        }
                        console.error('[StencilCutting] error:', err);
                    });
            }

            // ===== Init (like oral_examination) =====
            document.addEventListener('DOMContentLoaded', function () {
                loadSavedData();

                document.getElementById('add-stencil-cutting-question-paper-row')
                    .addEventListener('click', () => createRow());

                document.getElementById('form-list-of-stencil-cutting-question-paper')
                    .addEventListener('submit', function (e) {
                        e.preventDefault();
                        if (!validateForm()) {
                            if (window.Swal && Swal.fire) {
                                Swal.fire('Validation Error', 'Please select teacher(s) and enter stencil counts for all rows', 'error');
                            }
                            return;
                        }
                        if (window.Swal && Swal.fire) {
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
                        } else {
                            submitForm(this);
                        }
                    });
            });
        })();
    </script>
@endpush

