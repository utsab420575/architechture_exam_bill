@push('styles')
    <style>
        .card-list-of-advisor-student {
            background-color: white; /* starting point */
            transition: background-color 0.6s ease-in-out;
        }

        .card-list-of-advisor-student.fade-highlight {
            background-color: #28a745; /* strong green */
        }

        .card-list-of-advisor-student.fade-out {
            background-color: white;
        }

        select.is-invalid {
            border-color: red;
        }
    </style>
@endpush
<form id="form-list-of-advisor-student" action="{{ route('committee.input.advisor.student.store') }}" method="POST">
    @csrf
    <input type="hidden" value="{{$sid}}" name="sid">
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary ">
                <header class="card-header d-flex align-items-center">
                    <span class="step-badge">13</span>
                    <h2 class="card-title">Advisory (@***/- per student per semester):</h2>
                </header>

                <div class="card-body card-list-of-advisor-student">

                    <div class="row mb-2">
                        <div class="col-md-4 mb-4">
                            <div class="form-group">
                                <label for="advisor_per_student_rate">Per Student Per Semester Rate</label>
                                <input type="number"  name="advisor_per_student_rate" value="{{$advisor_per_student_rate??250}}" step="any" class="form-control" placeholder="Enter per student per semester rate" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                        </div>
                        <div class="col-md-4 mb-4">
                        </div>
                    </div>



                    @php
                        // Check if savedRateAssignAdvisorStudent is available and not empty
                        $savedAdvisorStudentData = $savedRateAssignAdvisorStudent ?? collect();
                    @endphp

                    {{--method call for find teacher name--}}
                    @php
                        function getTeacherName($teacherId, $teachers) {
                            $teacher = $teachers->firstWhere('id', $teacherId);
                            return $teacher ? $teacher->user->name . ' - ' . $teacher->designation->designation : 'Unknown';
                        }
                    @endphp
                    {{--this two column for heading--}}
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6 gap-2 fw-bold">
                                    Name
                                </div>
                                <div class="col-md-4 fw-bold">
                                    No of Students
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6 gap-2 fw-bold">
                                    Name
                                </div>
                                <div class="col-md-4 fw-bold">
                                    No of Students
                                </div>
                            </div>
                        </div>
                    </div>
                   {{-- this for making chunk to divide advisors into two column--}}
                    @php
                        $teacherStudents = $all_advisor_with_student_count->teacherstudent ?? [];

                          // Convert stdClass to array
                        $teacherStudentsArray = (array) $teacherStudents;

                        // Filter out advisors with no students
                        $filteredAdvisors = array_filter($teacherStudentsArray, function ($advisor) {
                            return !empty($advisor->students);
                        });

                        $count = count($filteredAdvisors);
                        $chunkSize = $count > 0 ? ceil($count / 2) : 1;

                        $chunks = array_chunk($filteredAdvisors, $chunkSize, true);
                    @endphp

                    <div class="row">
                        @foreach($chunks as $chunk)
                            <div class="col-md-6">
                                @foreach($chunk as $apiTeacherId => $singleAdvisor)
                                    @php
                                        // Get the email of the teacher from the API response
                                        $advisorEmail = $singleAdvisor->user->email ?? null;

                                        // Match the API teacher's email with the local teacher list
                                        //$teachers from controller ; it's not error here
                                        $matchedTeacher = $teachers->first(function ($localTeacher) use ($advisorEmail) {
                                            return isset($localTeacher->user->email) && $localTeacher->user->email === $advisorEmail;
                                        });

                                        // Use the local teacher's ID if matched, otherwise fall back to API ID
                                        $localTeacherId = $matchedTeacher ? $matchedTeacher->id : $apiTeacherId;

                                        // Use the matched local teacher's name, or fallback to API teacher's name or 'Unknown'
                                        $displayName = $matchedTeacher->user->name ?? $singleAdvisor->user->name ?? 'Unknown';


                                        // Search in saved data to find the teacher's total_students
                                        $savedAdvisorStudent = $savedAdvisorStudentData->firstWhere('teacher_id', $localTeacherId);

                                        // If the teacher exists in the saved data, use total_students, otherwise use API data
                                        $totalStudents = $savedAdvisorStudent ? $savedAdvisorStudent->total_students : $singleAdvisor->count;
                                    @endphp
                                    <div class="form-group row pb-3">
                                        <input type="hidden" name="advisorTeacherIds[]" value="{{ $localTeacherId }}">

                                        <div class="col-md-6">
                                            <label>{{ $displayName }}</label>
                                        </div>

                                        <div class="col-md-4">
                                            <input type="number"
                                                   name="advisorTotal_students[]"
                                                   min="0"
                                                   step="any"
                                                   value="{{ $totalStudents }}"
                                                   class="form-control"
                                                   required>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>


                    <div class="text-end mt-3">
                        <button id="submit-list-of-advisor-student" type="submit" class="btn btn-primary">
                            Submit Advisory Students
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
            const form = document.getElementById('form-list-of-advisor-student');

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                // ✅ Validate teacher selections
              /*  const teacherSelects = form.querySelectorAll('select[name^="teachers"]');
                let allSelected = true;

                teacherSelects.forEach(select => {
                    if (select.selectedOptions.length === 0) {
                        allSelected = false;
                        select.classList.add('is-invalid'); // red border if invalid
                    } else {
                        select.classList.remove('is-invalid');
                    }
                });

                if (!allSelected) {
                    Swal.fire({
                        title: 'Missing Teacher',
                        text: 'Please select at least one teacher for each course.',
                        icon: 'warning'
                    });
                    return; // ❌ stop form submission
                }*/
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

                                const submitBtn = document.getElementById('submit-list-of-advisor-student');
                                submitBtn.textContent = 'Update Advisory Students';  // ✅ New label
                                submitBtn.classList.remove('btn-primary');
                                submitBtn.classList.add('btn-warning');

                                const cards = document.querySelectorAll('.card-list-of-advisor-student');

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
                                    text: error.message||'Something went wrong. Please try again.',
                                    icon: 'error'
                                });
                            });
                    }
                });
            });
        });
    </script>
@endpush

