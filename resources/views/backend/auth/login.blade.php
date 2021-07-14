<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title> Admin | Login Page</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="" name="description" />
  <meta content="" name="author" /><link rel="icon" href="{{ asset('images/logo-header.svg') }}" type="image/gif" sizes="16x16">
  
	
	<!-- ================== BEGIN BASE CSS STYLE ================== -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
	<link href="{{ asset('/plugins/jquery-ui/jquery-ui.min.css') }}" rel="stylesheet" />
	<link href="{{ asset('/plugins/bootstrap/4.0.0/css/bootstrap.min.css') }}" rel="stylesheet" />
	<link href="{{ asset('/plugins/font-awesome/5.0/css/fontawesome-all.min.css') }}" rel="stylesheet" />
	<link href="{{ asset('/plugins/animate/animate.min.css') }}" rel="stylesheet" />
	<link href="{{ asset('/css/default/style.min.css') }}" rel="stylesheet" />
	<link href="{{ asset('/css/default/style-responsive.min.css') }}" rel="stylesheet" />
	<link href="{{ asset('/css/default/theme/default.css') }}" rel="stylesheet" id="theme" />
   <link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<!-- ================== END BASE CSS STYLE ================== -->
	
	<!-- ================== BEGIN BASE JS ================== -->
	<script src="{{ asset('/plugins/pace/pace.min.js') }}"></script>
	<!-- ================== END BASE JS ================== -->
  <script>
    var base_url = '{{ url('/') }}';
  </script>
</head>
<body class="pace-top">
	<!-- begin #page-loader -->
	<div id="page-loader" class="fade show"><span class="spinner"></span></div>
	<!-- end #page-loader -->
	
	<div class="login-cover">
    <div class="login-cover-image" style="background-image: url({{ asset('/img/login/login-bg-17.jpg') }})" data-id="login-cover-image"></div>
    <div class="login-cover-bg"></div>
	</div>
	<!-- begin #page-container -->
	<div id="page-container" class="fade">
    <!-- begin login -->
    <div class="login login-v2" data-pageload-addclass="animated fadeIn">
      <!-- begin brand -->
      <div class="login-header">
        <div class="brand">
          <strong>E-Ticket Management<strong>
          <!-- <small>welcome to my world</small> -->
        </div>
        <div class="icon">
          <i class="fa fa-lock"></i>
        </div>
      </div>
      <!-- end brand -->
      <!-- begin login-content -->
      <div class="login-content">
        <form id="FormLogin" method="POST" class="margin-bottom-0" action="{{ route('backend.auth.login') }}">
           @csrf
          <div class="form-group m-b-20">
            <input type="text" class="form-control form-control-lg" id="email" name="email" placeholder="Email" required  @error('email') is-invalid @enderror  value="{{ old('email') }}" required autocomplete="email" autofocus/>
            @error('email')
                <span class="text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
          </div>
          <div class="form-group m-b-20">
            <input type="password" class="form-control form-control-lg" id="password" @error('password') is-invalid @enderror name="password" required autocomplete="current-password" placeholder="Password" />
             @error('password')
                <span class="text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
              @enderror
          </div>
           <div class="checkbox checkbox-css m-b-20">
             <input type="checkbox" id="remember_checkbox" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}/> 
             <label for="remember_checkbox">
               Remember Me
             </label>
           </div>
          <div class="login-buttons">
            <button type="submit" class="btn btn-success btn-block btn-lg">เข้าสู่ระบบ</button>
          </div>
        </form>
      </div>
      <!-- end login-content -->
    </div>
    <!-- end login -->
	</div>
	<!-- end page container -->
	
	<!-- ================== BEGIN BASE JS ================== -->
	<script src="{{ asset('/plugins/jquery/jquery-3.2.1.min.js') }}"></script>
	<script src="{{ asset('/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
	<script src="{{ asset('/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js') }}"></script>
	<script src="{{ asset('/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
	<script src="{{ asset('/plugins/js-cookie/js.cookie.js') }}"></script>
	<script src="{{ asset('/js/theme/default.min.js') }}"></script>
	<script src="{{ asset('/js/apps.min.js') }}"></script>
  <script src="{{ asset('/plugins/bootstrap-sweetalert/sweetalert.min.js') }}"></script>

	<!-- ================== END BASE JS ================== -->
	
	<!-- ================== BEGIN PAGE LEVEL JS ================== -->

	<!-- ================== END PAGE LEVEL JS ================== -->
	<script>
		$(document).ready(function() {
			App.init();
		
		});
	</script>
</body>
</html>
