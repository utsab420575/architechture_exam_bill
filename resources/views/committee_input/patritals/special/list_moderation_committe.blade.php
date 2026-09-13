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
      action="{{ route('committee.input.special.examination.moderation.committee.store') }}" method="POST">
    @csrf
    <input type="hidden" id="{{$sid}}" name="sid" value="{{$sid}}">
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
                                <input type="number" name="moderation_committee_min_rate" value="{{$mc_min_rate??1500}}"
                                       step="any"
                                       class="form-control" placeholder="Min rate per member" required="">
                            </div>
                        </div>
                        <div class="col-md-4"></div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_week">Max rate per member:</label>
                                <input type="number" name="moderation_committee_max_rate" value="{{$mc_max_rate??5000}}"
                                       step="any"
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

                    <div class="mt-3 text-end">
                        <button type="button" id="add-moderation-committee-row" class="btn btn-sm btn-success me-2">+
                            Add Teacher
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
            const uniqueInputId = `amount_input_${moderationCommitteeRowCount}`;

            console.log(`🆕 Adding Teacher Row #${moderationCommitteeRowCount}`, { teacherId, amount, uniqueInputId });

            const container = document.getElementById('dynamic-moderation-container');
            const row = document.createElement('div');
            row.classList.add('row', 'align-items-center', 'mb-2');
            row.setAttribute('data-row', moderationCommitteeRowCount);

            row.innerHTML = `
            <div class="row mb-3 align-items-center" data-row="${moderationCommitteeRowCount}">
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
            </div>
        `;

            container.appendChild(row);

            // Select2 init
            $(row).find('select').select2({
                theme: 'bootstrap',
                width: '100%',
                allowClear: true,
                placeholder: '-- Select Teacher --'
            });

            row.querySelector('.remove-row').addEventListener('click', function () {
                console.log(`❌ Removing Row #${moderationCommitteeRowCount}`);
                row.remove();
                recalculateModerationAmounts();
            });

            if (amount === '') {
                console.log('🔁 Recalculating amounts after new row...');
                recalculateModerationAmounts();
            }
        }

        function recalculateModerationAmounts() {
            const minRate = parseFloat(document.querySelector('input[name="moderation_committee_min_rate"]').value) || 1500;
            const maxRate = parseFloat(document.querySelector('input[name="moderation_committee_max_rate"]').value) || 5000;
            const paperSetterRate = parseFloat(document.querySelector('input[name="paper_setter_rate"]')?.value) || 3600;

            const teacherInputs = document.querySelectorAll('#dynamic-moderation-container .amount-input');
            const totalTeachers = teacherInputs.length;

            console.log(`📊 Recalculating: min=${minRate}, max=${maxRate}, paperSetterRate=${paperSetterRate}, courses=${numberOfCourses}, teachers=${totalTeachers}`);

            if (totalTeachers === 0) return;

            let calculatedAmount = (paperSetterRate * 2 * numberOfCourses) / totalTeachers;
            calculatedAmount = Math.max(minRate, Math.min(maxRate, calculatedAmount));
            calculatedAmount = calculatedAmount.toFixed(2);

            teacherInputs.forEach((input, index) => {
                input.value = calculatedAmount;
                console.log(`   ➤ Amount for input #${index + 1} set to ${calculatedAmount}`);
            });
        }

        // Initial load
        if (savedModerationAssigns && savedModerationAssigns.length > 0) {
            console.log('📥 Loading saved moderation committee data...');
            savedModerationAssigns.forEach(assign => {
                createTeacherRow(assign.teacher_id, assign.total_amount);
            });
        }

        document.getElementById('add-moderation-committee-row').addEventListener('click', function () {
            console.log('➕ Add Teacher button clicked');
            createTeacherRow();
        });

        // Submit form
        document.getElementById('form-list-of-moderation-committee').addEventListener('submit', function (e) {
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

                if (!teacherId || teacherIds.includes(teacherId)) {
                    select.classList.add('is-invalid');
                    valid = false;
                }

                if (!amount || parseFloat(amount) <= 0) {
                    inputs[index].classList.add('is-invalid');
                    valid = false;
                }

                teacherIds.push(teacherId);
            });

            if (!valid) {
                console.warn('⚠️ Form validation failed');
                Swal.fire('Validation Failed', 'Ensure all teachers are selected, valid, and unique.', 'error');
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "Submit moderation committee data?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, submit',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(form);
                    console.log('📤 Submitting data:', Object.fromEntries(formData));

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
                            submitBtn.textContent = 'Update Moderation Committee';
                            submitBtn.classList.remove('btn-primary');
                            submitBtn.classList.add('btn-warning');

                            const cards = document.querySelectorAll('.card-list-of-moderation-committee');
                            cards.forEach(card => {
                                card.classList.add('fade-highlight');
                                setTimeout(() => card.classList.add('fade-out'), 1000);
                                setTimeout(() => card.classList.remove('fade-highlight', 'fade-out'), 1900);
                            });
                        })
                        .catch(error => {
                            console.error('❌ Submission Error:', error);
                            Swal.fire('Error!', error.message || 'Something went wrong.', 'error');
                        });
                }
            });
        });
    </script>
@endpush

