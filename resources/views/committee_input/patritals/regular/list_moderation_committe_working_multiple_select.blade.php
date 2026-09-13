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

<form id="form-list-of-moderation-committee" action="{{ route('committee.input.examination.moderation.committee.store') }}" method="POST">
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
                                <label for="min_rate">Min rate per member</label>
                                <input type="number" name="moderation_committee_min_rate" value="1500" step="any" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4"></div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="max_rate">Max rate per member</label>
                                <input type="number" name="moderation_committee_max_rate" value="5000" step="any" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2 fw-bold">
                        <div class="col-md-1 text-center">Select</div>
                        <div class="col-md-6">Teacher</div>
                        <div class="col-md-4">Amount (Taka)</div>
                    </div>

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
        $(document).ready(function() {
            $('select[data-plugin-selectTwo]').select2();
        });


        let rowCount = 0;
        const groupedTeachers = @json($groupedTeachers);

        function createRow() {
            rowCount++;
            const container = document.getElementById('dynamic-moderation-container');

            const row = document.createElement('div');
            row.className = 'row align-items-center mb-2';
            row.setAttribute('data-row', rowCount);

            let optionsHtml = '';
            for (const dept in groupedTeachers) {
                optionsHtml += `<optgroup label="${dept}">`;
                groupedTeachers[dept].forEach(t => {
                    const teacherName = t.user?.name ?? t.teachername;
                    const designation = t.designation?.designation ?? '';
                    optionsHtml += `<option value="${t.id}">${teacherName}, ${designation}</option>`;
                });
                optionsHtml += '</optgroup>';
            }

            row.innerHTML = `
        <div class="col-md-1 text-center">
            <input type="checkbox" class="form-check-input moderation-toggle" data-row="${rowCount}">
        </div>
        <div class="col-md-6">
            <select name="moderation_committee_teacher_ids[]" multiple data-plugin-selectTwo class="form-select populate teacher-select" data-row="${rowCount}" disabled required>
                ${optionsHtml}
            </select>
        </div>
        <div class="col-md-4">
            <input type="number" name="moderation_committee_amounts[]" class="form-control amount-input" placeholder="Provide Amount" disabled required>
        </div>
    `;

            container.appendChild(row);

            // Re-initialize Select2 for the new element
            $(row).find('select[data-plugin-selectTwo]').select2();

            // Toggle enable/disable on checkbox change
            row.querySelector('.moderation-toggle').addEventListener('change', function () {
                const index = this.dataset.row;
                const select = row.querySelector(`select[data-row="${index}"]`);
                const input = row.querySelector('.amount-input');

                select.disabled = !this.checked;
                input.disabled = !this.checked;

                if (!this.checked) {
                    $(select).val(null).trigger('change'); // Clear selected values
                    input.value = '';
                    select.classList.remove('is-invalid');
                    input.classList.remove('is-invalid');
                }
            });
        }



        document.getElementById('add-moderation-committee-row').addEventListener('click', createRow);
        document.getElementById('remove-moderation-committee-row').addEventListener('click', () => {
            const container = document.getElementById('dynamic-moderation-container');
            if (container.lastElementChild) {
                container.removeChild(container.lastElementChild);
                rowCount--;
            }
        });

        document.getElementById('form-list-of-moderation-committee').addEventListener('submit', function (e) {
            e.preventDefault();

            const checked = this.querySelectorAll('.moderation-toggle:checked');
            if (checked.length === 0) {
                Swal.fire('No Teachers Selected', 'Please select at least one teacher.', 'warning');
                return;
            }

            let isValid = true;
            const teacherIds = [];

            checked.forEach(chk => {
                const row = chk.closest('.row');
                const select = row.querySelector('.teacher-select');
                const amount = row.querySelector('.amount-input');

                select.classList.remove('is-invalid');
                amount.classList.remove('is-invalid');

                if (!select.value || teacherIds.includes(select.value)) {
                    select.classList.add('is-invalid');
                    isValid = false;
                }

                if (!amount.value || amount.value <= 0) {
                    amount.classList.add('is-invalid');
                    isValid = false;
                }

                teacherIds.push(select.value);
            });

            if (!isValid) {
                Swal.fire('Validation Error', 'Please fix errors before submitting.', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Submission',
                text: 'Do you want to submit this moderation committee?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, submit!',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    const formData = new FormData(this);

                    fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    })
                        .then(res => res.json())
                        .then(data => {
                            Swal.fire('Success', data.message, 'success');

                            const btn = document.getElementById('submit-list-of-moderation-committee');
                            btn.textContent = 'Already Saved';
                            btn.disabled = true;
                            btn.classList.remove('btn-primary');
                            btn.classList.add('btn-success');

                            document.querySelectorAll('.card-list-of-moderation-committee').forEach(card => {
                                card.classList.add('fade-highlight');
                                setTimeout(() => card.classList.add('fade-out'), 1000);
                                setTimeout(() => card.classList.remove('fade-highlight', 'fade-out'), 1900);
                            });
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', err.message || 'Failed to submit. Try again.', 'error');
                        });
                }
            });
        });
    </script>

@endpush
