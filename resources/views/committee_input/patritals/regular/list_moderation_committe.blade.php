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

<form id="form-list-of-moderation-committee"
      action="{{ route('committee.input.regular.examination.moderation.committee.store') }}" method="POST">
    @csrf
    {{--<input type="hidden" id="{{$sid}}" name="sid" value="{{$sid}}">--}}
    <input type="hidden"  name="sid" value="{{$sid}}">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header">
                    <h2 class="card-title d-flex align-items-center">
                        <span class="step-badge">1</span>
                        List of Examination Committee/Moderation Committee Members @ min***
                    </h2>
                </header>

                <div class="card-body card-list-of-moderation-committee">
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_week">Min rate per member</label>
                                <input type="number" name="moderation_committee_min_rate" value="{{$mc_min_rate??2000}}" step="any"
                                       class="form-control" placeholder="Min rate per member" required="">
                            </div>
                        </div>
                        <div class="col-md-4"></div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_week">Max rate per member:</label>
                                <input type="number" name="moderation_committee_max_rate" value="{{$mc_max_rate??5000}}" step="any"
                                       class="form-control" placeholder="Max rate per member" required="">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row mb-2 fw-bold mt-2">
                        <div class="col-md-8 text-start">Select Teacher</div>
                        <div class="col-md-3 text-start" style="margin-left:-15px;">Amount(Taka)</div>
                    </div>

                    {{--here will be add checkbox--}}
                    <div id="dynamic-moderation-container"></div>

                    {{--Only Add Button--}}
                    <div class="mt-3 text-end">
                        <button type="button" id="add-moderation-committee-row" class="btn btn-sm btn-success">+ Add
                            Teacher
                        </button>
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
        const numberOfCourses = {{ $number_of_theory_courses }};
        let moderationCommitteeRowCount = 0;
        const moderationCommitteeTeachers = @json($teachers);
        const savedModerationAssigns = @json($savedModerationAssigns);

        function createTeacherRow(teacherId = '', amount = '') {
            moderationCommitteeRowCount++;
            console.log(`🔧 Creating row #${moderationCommitteeRowCount}`, { teacherId, amount });

            const container = document.getElementById('dynamic-moderation-container');
            const row = document.createElement('div');
            row.classList.add('row', 'mb-3', 'align-items-center');
            row.setAttribute('data-row', moderationCommitteeRowCount);

            const uniqueInputId = `amount_input_${moderationCommitteeRowCount}`;

            row.innerHTML = `
            <div class="col-md-8">
                <select name="moderation_committee_teacher_ids[]" class="form-control teacher-select" required>
                    <option value="">-- Select Teacher --</option>
                    ${moderationCommitteeTeachers.map(t => `
                        <option value="${t.id}" ${t.id == teacherId ? 'selected' : ''}>
                            ${t.user.name}, ${t.designation.designation}, ${t.department.shortname}
                        </option>
                    `).join('')}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" id="${uniqueInputId}" name="moderation_committee_amounts[]" class="form-control amount-input"
                       placeholder="Provide Amount" value="${amount}" step="0.01" required>
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-row">🗑️</button>
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

            // Handle remove
            row.querySelector('.remove-row').addEventListener('click', function () {
                row.remove();
                console.log('🗑️ Row removed. Recalculating...');
                recalculateModerationAmounts();
            });

            if (amount === '') {
                console.log('🧮 No preset amount. Triggering recalculation...');
                recalculateModerationAmounts();
            }
        }

        function recalculateModerationAmounts() {
            console.log('📊 recalculateModerationAmounts() triggered');

            const minRate = parseFloat(document.querySelector('input[name="moderation_committee_min_rate"]').value) || 1500;
            const maxRate = parseFloat(document.querySelector('input[name="moderation_committee_max_rate"]').value) || 5000;
            const paperSetterRate = parseFloat(document.querySelector('input[name="paper_setter_rate"]')?.value || 3600);

            const teacherInputs = document.querySelectorAll('#dynamic-moderation-container .amount-input');
            const totalTeachers = teacherInputs.length;

            console.log({
                minRate,
                maxRate,
                paperSetterRate,
                numberOfCourses,
                totalTeachers
            });

            if (totalTeachers === 0) {
                console.warn('⚠️ No teacher rows found.');
                return;
            }

            let calculatedAmount = (paperSetterRate * 2 * numberOfCourses) / totalTeachers;
            calculatedAmount = Math.max(minRate, Math.min(calculatedAmount, maxRate)).toFixed(2);

            teacherInputs.forEach((input, i) => {
                input.value = calculatedAmount;
                console.log(`  ➤ Updated Row #${i + 1} with ${calculatedAmount}`);
            });
        }

        // Preload DB data
        if (savedModerationAssigns?.length > 0) {
            console.log('📥 Loading saved rows...');
            savedModerationAssigns.forEach(assign => {
                createTeacherRow(assign.teacher_id, assign.total_amount);
            });
        }

        // Add new blank row
        document.getElementById('add-moderation-committee-row').addEventListener('click', () => {
            console.log('➕ "+ Add Teacher" clicked');
            createTeacherRow();
        });

        // Submit
        document.getElementById('form-list-of-moderation-committee').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
            const selects = form.querySelectorAll('.teacher-select');
            const inputs = form.querySelectorAll('.amount-input');
            let valid = true;
            const teacherIds = [];

            selects.forEach((select, index) => {
                const teacherId = select.value;
                const amount = parseFloat(inputs[index].value);

                select.classList.remove('is-invalid');
                inputs[index].classList.remove('is-invalid');

                if (!teacherId || teacherIds.includes(teacherId)) {
                    select.classList.add('is-invalid');
                    valid = false;
                }

                if (isNaN(amount) || amount <= 0) {
                    inputs[index].classList.add('is-invalid');
                    valid = false;
                }

                teacherIds.push(teacherId);
            });

            if (!valid) {
                Swal.fire('Validation Failed', 'Ensure each teacher is selected, unique, and has a valid amount.', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Submission',
                text: 'Submit moderation committee data?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Submit',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    const formData = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                        .then(res => res.ok ? res.json() : res.json().then(err => Promise.reject(err)))
                        .then(data => {
                            Swal.fire('Success!', data.message, 'success');

                            const submitBtn = document.getElementById('submit-list-of-moderation-committee');
                            submitBtn.textContent = 'Update Moderation Committee';
                            submitBtn.classList.remove('btn-primary');
                            submitBtn.classList.add('btn-warning');

                            document.querySelectorAll('.card-list-of-moderation-committee').forEach(card => {
                                card.classList.add('fade-highlight');
                                setTimeout(() => card.classList.add('fade-out'), 1000);
                                setTimeout(() => card.classList.remove('fade-highlight', 'fade-out'), 1900);
                            });
                        })
                        .catch(err => {
                            Swal.fire('Error!', err.message || 'Unexpected error occurred.', 'error');
                        });
                }
            });
        });
    </script>
@endpush



