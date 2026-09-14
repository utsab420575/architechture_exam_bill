@push('styles')
    <style>
        .card-list-of-tabulation {
            background-color: white; /* starting point */
            transition: background-color 0.6s ease-in-out;
        }

        .card-list-of-tabulation.fade-highlight {
            background-color: #28a745; /* strong green */
        }

        .card-list-of-tabulation.fade-out {
            background-color: white;
        }

        select.is-invalid {
            border-color: red;
        }
    </style>
@endpush
<form id="form-list-of-tabulation" action="{{ route('committee.input.tabulation.store') }}" method="POST">
    @csrf
    <input type="hidden" id="sid" name="sid" value="{{$sid}}">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary ">
                <header class="card-header d-flex align-items-center">
                    <h2 class="card-title">
                        <span class="step-badge">8.e</span>
                        Tabulation(@ {{ $tabulation_per_student_rate ?? 90 }}/- per student)</h2>
                </header>

                <div class="card-body card-list-of-tabulation">
                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="tabulation_rate">Per Student Rate</label>
                                <input type="number" name="tabulation_rate" id="tabulation_rate" value="{{ $tabulation_per_student_rate ?? 90 }}" step="any" class="form-control" placeholder="Enter per student rate" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                        </div>
                        <div class="col-md-4 mb-4">
                        </div>
                    </div>

                    <div class="form-group row pb-3">
                        @php
                            $savedForTabulation = $savedRateAssignTabulation ?? collect(); // Collection of RateAssigns
                        @endphp
                        <div class="col-md-9">
                            <label for="tabulation_teachers">Select Tabulators</label>
                            <select name="tabulation_teachers[]"
                                    multiple data-plugin-selectTwo
                                    id="tabulation_teachers"
                                    class="form-control populate" required>
                                <option value="" disabled>-- Select Teacher --</option>
                                @foreach($groupedTeachers as $deptFullName => $deptTeachers)
                                    <optgroup label="{{ $deptFullName }}">
                                        @foreach($deptTeachers as $teacher)
                                            <option value="{{ $teacher->id }}" {{ $savedForTabulation->pluck('teacher_id')->contains($teacher->id) ? 'selected' : '' }}>
                                                {{ $teacher->user->name ?? 'N/A' }}  - {{ $teacher->department->shortname ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        {{-- Total Students --}}
                        <div class="col-md-3">
                            @php
                                // Check if there is saved data, and if yes, get total_students from the first teacher's entry
                                $noOfItems = $savedForTabulation->isNotEmpty()
                                            ? $savedForTabulation->first()->total_students
                                            : $totalStudentInSession;
                            @endphp
                            <label for="tabulation_total_students">Total Students</label>
                            <input type="number"
                                   name="tabulation_total_students"
                                   id="tabulation_total_students"
                                   min="0"
                                   step="any"
                                   value="{{ old('tabulation_total_students', $noOfItems) }}"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button id="submit-list-of-tabulation" type="submit" class="btn btn-primary">
                            Submit Tabulation Committee
                        </button>
                    </div>
                </div>

            </section>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('form-list-of-tabulation');

            form.addEventListener('submit', function (e) {
                e.preventDefault();

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
                                    // Return the error JSON and throw it
                                    return response.json().then(err => {
                                        throw new Error(err.message || 'Unknown error occurred.');
                                    });
                                }
                                return response.json(); // if response is OK
                            })
                            .then(data => {
                                console.log("Server response:", data); // Debug log
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });

                                const submitBtn = document.getElementById('submit-list-of-tabulation');
                                submitBtn.textContent = 'Update Tabulation Committee';
                                submitBtn.classList.remove('btn-primary');
                                submitBtn.classList.add('btn-warning');

                                const cards = document.querySelectorAll('.card-list-of-tabulation');

                                cards.forEach(card => {
                                    card.classList.add('fade-highlight');

                                    setTimeout(() => {
                                        card.classList.add('fade-out');
                                    }, 1000);

                                    setTimeout(() => {
                                        card.classList.remove('fade-highlight', 'fade-out');
                                    }, 1900);
                                });
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: error.message || 'Something went wrong. Please try again.',
                                    icon: 'error'
                                });
                            });
                    }
                });
            });
        });
    </script>
@endpush
