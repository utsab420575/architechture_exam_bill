@push('styles')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

<form id="form-list-of-moderation-committee" action="{{ route('committee.input.regular.examination.moderation.committee.store') }}" method="POST">
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
                                <label for="total_week">Min rate per member</label>
                                <input type="number"  name="moderation_committee_min_rate" value="1500" step="any" class="form-control" placeholder="Min rate per member" required="">
                            </div>
                        </div>
                        <div class="col-md-4"></div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_week">Max rate per member:</label>
                                <input type="number"  name="moderation_committee_max_rate" value="5000" step="any" class="form-control" placeholder="Max rate per member" required="">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2 fw-bold">
                        <div class="col-md-1 text-center">Select</div>
                        <div class="col-md-6">Teacher</div>
                        <div class="col-md-4">Amount(Taka)</div>
                    </div>


                    <div class="col-sm-9">
                            <select id="moderation_committee_teacher_ids" name="moderation_committee_teacher_ids" data-plugin-selectTwo class="form-control populate" title="Please select at least one state" required>
                                <option value="">Choose a State</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{$teacher->id}}">
                                        {{ $teacher->user->name ?? $teacher->teachername }} ({{$teacher->designation->designation}} {{$teacher->department->shortname}})
                                    </option>
                                @endforeach
                            </select>
                        </div>








                    <div class="col-md-9 mt-5">
                        <select id="teachers" name="teachers[]"  multiple data-plugin-selectTwo class="form-control  populate">
                            @foreach($groupedTeachers as $deptFulltName => $deptTeachers)
                                <optgroup label="{{ $deptFulltName }}">
                                    @foreach($deptTeachers as $teacher)
                                        <option value="{{ $teacher->id }}">
                                            {{ $teacher->user->name ?? $teacher->teachername }}({{$teacher->designation->designation}})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>


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

@endpush
