<!doctype html>
<html class="fixed">
<head>
    <!-- Basic -->
    <meta charset="UTF-8">
    <meta name="keywords" content="HTML5 Admin Template" />
    <meta name="description" content="Porto Admin - Responsive HTML5 Template">
    <meta name="author" content="okler.net">
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <!-- Web Fonts  -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/bootstrap/css/bootstrap.css')}}"/>
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/animate/animate.compat.css')}}">
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/font-awesome/css/all.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/boxicons/css/boxicons.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/magnific-popup/magnific-popup.css')}}"/>
    <link rel="stylesheet" href="{{asset('backend/assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.css')}}" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/css/theme.css')}}"/>
    <!-- Skin CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/css/skins/default.css')}}"/>
    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/css/custom.css')}}">
    <!-- Head Libs -->
    <script src="{{asset('backend/assets/backend/assets/vendor/modernizr/modernizr.js')}}"></script>

</head>
<body>
<!-- start: page -->
<section class="body-sign">
    <div class="center-sign">
       {{-- <a href="/" class="logo float-start">
            <img src="{{asset('backend/assets/img/logo.png')}}" height="70" alt="Porto Admin" />
        </a>--}}
        <div class="panel card-sign">
            <div class="card-title-sign mt-3 text-end">
                <h2 class="title text-uppercase font-weight-bold m-0"><i class="bx bx-user-circle me-1 text-6 position-relative top-5"></i> Sign Up</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{route('register')}}" >
                    @csrf
                    {{--Name--}}
                    <div class="form-group mb-3">
                        <label>Name</label>
                        <input name="name" id="name" type="text" class="form-control form-control-lg" value="{{old('name')}}" required/>
                        @error('name')
                            <span class="text-danger"> {{ $message }} </span>
                        @enderror
                    </div>
                    {{--Email--}}
                    <div class="form-group mb-3">
                        <label>E-mail Address</label>
                        <input name="email" id="email" type="email" class="form-control form-control-lg" value="{{old('email')}}"required/>
                        @error('email')
                        <span class="text-danger"> {{ $message }} </span>
                        @enderror
                    </div>
                    {{--Phone--}}
                    <div class="form-group mb-3">
                        <label>Phone Number(01*********)</label>
                        <input name="phone" id="phone" type="tel" class="form-control form-control-lg" value="{{old('phone')}}" required />
                        @error('phone')
                            <span class="text-danger"> {{ $message }} </span>
                        @enderror
                    </div>
                    {{--Password--}}
                    <div class="form-group mb-0">
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label>Password</label>
                                <input name="password" id="password" type="password" class="form-control form-control-lg" required/>
                                @error('password')
                                    <span class="text-danger"> {{ $message }} </span>
                                @enderror
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label>Password Confirmation</label>
                                <input name="password_confirmation" id="password_confirmation" type="password" class="form-control form-control-lg" required />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            {{--<div class="checkbox-custom checkbox-default">
                                <input id="AgreeTerms" name="agreeterms" type="checkbox"/>
                                <label for="AgreeTerms">I agree with <a href="#">terms of use</a></label>
                            </div>--}}
                        </div>
                        <div class="col-sm-4 text-end">
                            <button type="submit" class="btn btn-primary mt-2">Sign Up</button>
                        </div>
                    </div>
                    {{--<span class="mt-3 mb-3 line-thru text-center text-uppercase">
								<span>or</span>
							</span>
                    <div class="mb-1 text-center">
                        <a class="btn btn-facebook mb-3 ms-1 me-1" href="#">Connect with <i class="fab fa-facebook-f"></i></a>
                    </div>--}}
                    <p class="text-center">Already have an account? <a href="{{route('login')}}">Sign In!</a></p>
                </form>
            </div>
        </div>
        <p class="text-center text-muted mt-3 mb-3">&copy; Copyright {{ date('Y') }}. All Rights Reserved.</p>
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
</body>
</html>
