<!doctype html>
<html class="fixed">
<head>
    <!-- Basic -->
    <meta charset="UTF-8">
    <meta name="keywords" content="HTML5 Admin Template"/>
    <meta name="description" content="Porto Admin - Responsive HTML5 Template">
    <meta name="author" content="okler.net">
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <!-- Web Fonts  -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800|Shadows+Into+Light"
          rel="stylesheet" type="text/css">
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/bootstrap/css/bootstrap.css')}}"/>
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/animate/animate.compat.css')}}">
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/font-awesome/css/all.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/boxicons/css/boxicons.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/magnific-popup/magnific-popup.css')}}"/>
    <link rel="stylesheet"
          href="{{asset('backend/assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.css')}}"/>
    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/css/theme.css')}}"/>
    <!-- Skin CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/css/skins/default.css')}}"/>
    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/css/custom.css')}}">
    <!-- Head Libs -->
    <script src="{{asset('backend/assets/vendor/modernizr/modernizr.js')}}"></script>

    <!-- Toaster Css-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
</head>
<body>
<!-- start: page -->
<section class="body-sign">
    <div class="center-sign">
        {{--<a href="/" class="logo float-start">
            <img src="img/logo.png" height="70" alt="Porto Admin" />
        </a>--}}
        <div class="panel card-sign">
            <div class="card-title-sign mt-3 text-end">
                <h2 class="title text-uppercase font-weight-bold m-0"><i
                        class="bx bx-user-circle me-1 text-6 position-relative top-5"></i> Sign In</h2>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{route('login')}}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Email/Phone</label>
                        <div class="input-group">
                            <input id="login"
                                   name="login" required=""
                                   placeholder="Enter your email/phone number"
                                   type="text"
                                   class="form-control form-control-lg"
                                   value="{{old('login')}}"
                            />
                            <span class="input-group-text">
										<i class="bx bx-user text-4"></i>
									</span>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <div class="clearfix">
                            <label class="float-start">Password</label>
                            {{--<a href="pages-recover-password.html" class="float-end">Lost Password?</a>--}}
                        </div>
                        <div class="input-group">
                            <input name="password" id="password" type="password" class="form-control form-control-lg"
                                   placeholder="Enter your password" required/>
                            <span class="input-group-text">
										<i class="bx bx-lock text-4"></i>
									</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="checkbox-custom checkbox-default">
                                <input id="rememberme" name="rememberme" type="checkbox"  checked/>
                                <label for="RememberMe">Remember Me</label>
                            </div>
                        </div>
                        <div class="col-sm-4 text-end">
                            <button type="submit" class="btn btn-primary mt-2">Sign In</button>
                        </div>
                    </div>
                    {{-- <span class="mt-3 mb-3 line-thru text-center text-uppercase">
                                 <span>or</span>
                             </span>--}}
                    {{--<div class="mb-1 text-center">
                        <a class="btn btn-facebook mb-3 ms-1 me-1" href="#">Connect with <i class="fab fa-facebook-f"></i></a>
                    </div>--}}

                    {{--signup option remove--}}
                    {{--<p class="text-center">Don't have an account yet? <a href="{{route('register')}}">Sign Up!</a></p>--}}
                </form>
            </div>
        </div>
        <p class="text-center text-muted mt-3 mb-3">
            &copy; Copyright {{ date('Y') }}. All Rights Reserved.
        </p>
    </div>
</section>
<!-- end: page -->
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
<!-- Theme Base, Components and Settings -->
<script src="{{asset('backend/assets/js/theme.js')}} "></script>
<!-- Theme Custom -->
<script src="{{asset('backend/assets/js/custom.js')}} "></script>
<!-- Theme Initialization Files -->
<script src="{{asset('backend/assets/js/theme.init.js')}} "></script>

<!-- Toaster js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


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
</body>
</html>
