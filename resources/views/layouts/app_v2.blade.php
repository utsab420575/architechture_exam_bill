<!doctype html>
<html class="fixed header-dark">
<head>
    <!-- Basic -->
    <meta charset="UTF-8">
    <title>Architecture Exam bill</title>
    <meta name="keywords" content="HTML5 Admin Template" />
    <meta name="description" content="Porto Admin - Responsive HTML5 Template">
    <meta name="author" content="okler.net">
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <!-- Web Fonts  -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

    <link rel="icon" type="image/png" href="{{ asset('backend/assets/img/logo3.png') }}">


    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/bootstrap/css/bootstrap.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/animate/animate.compat.css')}}">
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/font-awesome/css/all.min.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/boxicons/css/boxicons.min.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/magnific-popup/magnific-popup.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/select2/css/select2.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/select2-bootstrap-theme/select2-bootstrap.min.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/datatables/media/css/dataTables.bootstrap5.css')}}" />

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/jquery-ui/jquery-ui.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/jquery-ui/jquery-ui.theme.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/bootstrap-multiselect/css/bootstrap-multiselect.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/morris/morris.css')}}" />


    <link rel="stylesheet" href="{{asset('backend/assets/vendor/select2/css/select2.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/select2-bootstrap-theme/select2-bootstrap.min.css')}}" />


    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/css/theme.css')}}" />
    <!-- Skin CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/css/skins/default.css')}}" />
    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/css/custom.css')}}">
    <!-- Head Libs -->
    <script src="{{asset('backend/assets/vendor/modernizr/modernizr.js')}}"></script>





    <!-- Toaster Css-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">


    {{--this is for styling serial number of form--}}
    <style>
        .step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.8rem;              /* make bigger for more padding */
            height: 2.8rem;             /* make bigger for more padding */
            border-radius: 999px;
            background: #079992;      /* green */
            color: #fff;
            font-weight: 800;
            font-size: 1.1rem;
            margin-right: .5rem;      /* space after each badge */
        }
        .step-badge.grey { background: #4b6584; }
    </style>


    @stack('styles')

    @yield('styles')
</head>
<body>
<section class="body">
    <!-- start: header -->
    @include('partials.header')
    <!-- end: header -->
    <div class="inner-wrapper">
        <!-- start: sidebar -->
        @include('partials.sidebar')
        <!-- end: sidebar -->
        {{--start body--}}
        @yield('content')
       {{-- end body--}}
    </div>
</section>


<!-- Vendor -->
<script src="{{asset('backend/assets/vendor/jquery/jquery.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js')}}"></script>
<script src="{{asset('backend/assets/vendor/popper/umd/popper.min.js')}}"></script>
<script src="{{asset('backend/assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('backend/assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('backend/assets/vendor/common/common.js')}}"></script>
<script src="{{asset('backend/assets/vendor/nanoscroller/nanoscroller.js')}}"></script>
<script src="{{asset('backend/assets/vendor/magnific-popup/jquery.magnific-popup.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jquery-placeholder/jquery.placeholder.js')}}"></script>


<!-- Specific Page Vendor -->
<script src="{{asset('backend/assets/vendor/select2/js/select2.js')}}"></script>
<script src="{{asset('backend/assets/vendor/datatables/media/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backend/assets/vendor/datatables/media/js/dataTables.bootstrap5.min.js')}}"></script>
<script src="{{asset('backend/assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('backend/assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.bootstrap4.min.js')}}"></script>
<script src="{{asset('backend/assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.html5.min.js')}}"></script>
<script src="{{asset('backend/assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.print.min.js')}}"></script>
<script src="{{asset('backend/assets/vendor/datatables/extras/TableTools/JSZip-2.5.0/jszip.min.js')}}"></script>
<script src="{{asset('backend/assets/vendor/datatables/extras/TableTools/pdfmake-0.1.32/pdfmake.min.js')}}"></script>
<script src="{{asset('backend/assets/vendor/datatables/extras/TableTools/pdfmake-0.1.32/vfs_fonts.js')}}"></script>



<script src="{{asset('backend/assets/vendor/select2/js/select2.js')}}"></script>
<!-- Specific Page Vendor -->
<script src="{{asset('backend/assets/vendor/jquery-ui/jquery-ui.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jqueryui-touch-punch/jquery.ui.touch-punch.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jquery-appear/jquery.appear.js')}}"></script>
<script src="{{asset('backend/assets/vendor/bootstrap-multiselect/js/bootstrap-multiselect.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jquery.easy-pie-chart/jquery.easypiechart.js')}}"></script>
<script src="{{asset('backend/assets/vendor/flot/jquery.flot.js')}}"></script>
<script src="{{asset('backend/assets/vendor/flot.tooltip/jquery.flot.tooltip.js')}}"></script>
<script src="{{asset('backend/assets/vendor/flot/jquery.flot.pie.js')}}"></script>
<script src="{{asset('backend/assets/vendor/flot/jquery.flot.categories.js')}}"></script>
<script src="{{asset('backend/assets/vendor/flot/jquery.flot.resize.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jquery-sparkline/jquery.sparkline.js')}}"></script>
<script src="{{asset('backend/assets/vendor/raphael/raphael.js')}}"></script>
<script src="{{asset('backend/assets/vendor/morris/morris.js')}}"></script>
<script src="{{asset('backend/assets/vendor/gauge/gauge.js')}}"></script>
<script src="{{asset('backend/assets/vendor/snap.svg/snap.svg.js')}}"></script>
<script src="{{asset('backend/assets/vendor/liquid-meter/liquid.meter.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jqvmap/jquery.vmap.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jqvmap/data/jquery.vmap.sampledata.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jqvmap/maps/jquery.vmap.world.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jqvmap/maps/continents/jquery.vmap.africa.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jqvmap/maps/continents/jquery.vmap.asia.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jqvmap/maps/continents/jquery.vmap.australia.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jqvmap/maps/continents/jquery.vmap.europe.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jqvmap/maps/continents/jquery.vmap.north-america.js')}}"></script>
<script src="{{asset('backend/assets/vendor/jqvmap/maps/continents/jquery.vmap.south-america.js')}}"></script>


<!-- Theme Base, Components and Settings -->
<script src="{{asset('backend/assets/js/theme.js')}} "></script>
<!-- Theme Custom -->
<script src="{{asset('backend/assets/js/custom.js')}} "></script>
<!-- Theme Initialization Files -->
<script src="{{asset('backend/assets/js/theme.init.js')}} "></script>



<!-- Examples -->
<script src="{{asset('backend/assets/js/examples/examples.dashboard.js')}} "></script>



<!-- Toaster js -->
<!-- if we add jquery 3.6.0 this will cause error ; beacuse we already added jquery from backend/assets/vendor/jquery/jquery.js-->
{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Sweetalert-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Examples -->
<script src="{{asset('backend/assets/js/examples/examples.datatables.default.js')}}"></script>
<script src="{{asset('backend/assets/js/examples/examples.datatables.row.with.details.js')}}"></script>
<script src="{{asset('backend/assets/js/examples/examples.datatables.tabletools.js')}}"></script>

<script>
    @if(Session::has('message'))
        toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "4000"
    };

    var type = "{{ Session::get('alert-type', 'info') }}";
    switch (type) {
        case 'info':
            toastr.info("{{ Session::get('message') }}");
            break;

        case 'success':
            toastr.success("{{ Session::get('message') }}");
            break;

        case 'warning':
            toastr.warning("{{ Session::get('message') }}");
            break;

        case 'error':
            toastr.error("{{ Session::get('message') }}");
            break;
    }
    @endif
</script>

{{--Sweetalert--}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('click', function (event) {
            // Check if the clicked element or its parent has the 'delete' class
            const target = event.target.closest('.delete');
            if (target) {
                event.preventDefault(); // Prevent default action

                // Get the deletion URL
                const deleteUrl = target.getAttribute('href');

                // Show SweetAlert2 confirmation
                Swal.fire({
                    title: "Are you sure?",
                    text: "This action cannot be undone!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "Cancel",
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect to the deletion URL
                        window.location.href = deleteUrl;
                    }
                });
            }
        });
    });
</script>

{{--this is for copying data from one blade to another--}}
<script>
    function wireCopyAcrossForms({
                                     srcTeacherPrefix,  // e.g. 'prepares_theory_grade_sheet_teacher_ids'
                                     srcCountPrefix,    // e.g. 'prepares_theory_grade_sheet_no_of_students'
                                     dstTeacherPrefix,  // e.g. 'scrutinizing_theory_grade_sheet_teacher_ids'
                                     dstCountPrefix,    // e.g. 'scrutinizing_theory_grade_sheet_no_of_students'
                                     checkboxId         // e.g. 'copy-from-form1-all-theory'
                                 }) {
        const master = document.getElementById(checkboxId);
        if (!master) return;

        const getCourseId = (name) => (name.match(/\[(\d+)\]/) || [])[1];

        const readSrcMap = () => {
            const map = {};
            document.querySelectorAll(`select[name^="${srcTeacherPrefix}["]`).forEach(sel => {
                const cid = getCourseId(sel.name);
                if (!cid) return;
                map[cid] = {
                    values: Array.from(sel.selectedOptions).map(o => o.value),
                    students: (document.querySelector(`input[name="${srcCountPrefix}[${cid}]"]`) || {}).value || ''
                };
            });
            return map;
        };

        const applyToDest = (srcMap) => {
            document.querySelectorAll(`select[name^="${dstTeacherPrefix}["]`).forEach(dstSel => {
                const cid = getCourseId(dstSel.name);
                if (!cid || !srcMap[cid]) return;
                const { values, students } = srcMap[cid];

                // ensure options exist (edge case)
                values.forEach(v => {
                    if (!dstSel.querySelector(`option[value="${v}"]`)) {
                        dstSel.add(new Option(v, v, false, false));
                    }
                });

                if (window.$ && $(dstSel).data('select2')) {
                    $(dstSel).val(values).trigger('change');
                } else {
                    Array.from(dstSel.options).forEach(o => o.selected = values.includes(o.value));
                }

                const dstCount = document.querySelector(`input[name="${dstCountPrefix}[${cid}]"]`);
                if (dstCount && !dstCount.value && (students ?? '') !== '') {
                    dstCount.value = students; // only fill if empty
                }
            });
        };

        master.addEventListener('change', () => {
            if (!master.checked) return;
            const srcMap = readSrcMap();
            applyToDest(srcMap);
        });
    }
</script>

@stack('scripts')
</body>
</html>
