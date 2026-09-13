<form id="form-select-scrutinizers-8c-8d-10a-10b" class="form-select-scrutinizers">
    @csrf
    <div class="row mb-5">
        <div class="col-md-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header d-flex align-items-center flex-wrap">
                    <h2 class="card-title mb-0">
                        <span class="step-badge">Copy</span>
                        Set Teacher for 10.a, 10.b, 8.d, 8.c
                    </h2>

                    <!-- Toggle group -->
                    <div class="ms-auto d-flex flex-wrap gap-3">
                        <!-- 10.a -->
                        <label class="copy-toggle" title="Copy selected teachers to 10.a (Scrutinizing Theoretical)">
                            <input type="checkbox" id="copy-in-scrutinizing-theory-grade-sheet">
                            <span class="track"><span class="knob"></span></span>
                            <span class="label-text">Copy → 10.a</span>
                        </label>

                        <!-- 10.b -->
                        <label class="copy-toggle" title="Copy selected teachers to 10.b (Scrutinizing Sessional)">
                            <input type="checkbox" id="copy-in-scrutinizing-sessional-grade">
                            <span class="track"><span class="knob"></span></span>
                            <span class="label-text">Copy → 10.b</span>
                        </label>

                        <!-- 8.d -->
                        <label class="copy-toggle" title="Copy selected teachers to 8.d (Prepared Computerized Result)">
                            <input type="checkbox" id="copy-in-computerized-result-theory">
                            <span class="track"><span class="knob"></span></span>
                            <span class="label-text">Copy → 8.d</span>
                        </label>

                    </div>
                </header>

                <div class="card-body card-list-of-verified-computerized-result">
                    <div class="form-group row pb-3">
                        <div class="col-md-9">
                            <label for="selected_teacher_ids_8c_8d_10a_10b">Select Scrutinizers</label>
                            <select name="selected_teacher_ids_8c_8d_10a_10b[]"
                                    id="selected_teacher_ids_8c_8d_10a_10b"
                                    class="form-control populate"
                                    data-plugin-selectTwo multiple>
                                <option value="" disabled>-- Select Teacher --</option>
                                @foreach($groupedTeachers as $deptFullName => $deptTeachers)
                                    <optgroup label="{{ $deptFullName }}">
                                        @foreach($deptTeachers as $teacher)
                                            <option value="{{ $teacher->id }}">
                                                {{ $teacher->user->name ?? 'N/A' }} - {{ $teacher->department->shortname ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 10.a — Scrutinizing (Theoretical)
            wireCopySelectedTeachers({
                sourceSelectId: 'selected_teacher_ids_8c_8d_10a_10b',
                destPrefixes:   ['scrutinizing_theory_grade_sheet_teacher_ids'],
                triggerId:      'copy-in-scrutinizing-theory-grade-sheet',
                mode:           'replace'
            });

            // 10.b — Scrutinizing (Sessional)
            wireCopySelectedTeachers({
                sourceSelectId: 'selected_teacher_ids_8c_8d_10a_10b',
                destPrefixes:   ['scrutinizing_sessional_grade_sheet_teacher_ids'],
                triggerId:      'copy-in-scrutinizing-sessional-grade',
                mode:           'replace'
            });

            // 8.d — Prepared Computerized Result
            wireCopySelectedTeachers({
                sourceSelectId: 'selected_teacher_ids_8c_8d_10a_10b',
                destPrefixes:   ['prepared_computerized_result_teacher_ids'],
                triggerId:      'copy-in-computerized-result-theory',
                mode:           'replace'
            });
        });
    </script>
@endpush
