<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Log in</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/dist/css/AdminLTE.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/plugins/iCheck/square/blue.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  <style type="text/css">
      .login-box-body{
        border: 2px;
        border-radius: 5px;
        background: white;
      }

      .bg {
          /* The image used */
          background-image: url("{{url('/')}}/public/images/background.jpg");

          /* Full height */
          height: 100vh;

          /* Center and scale the image nicely */
          background-position: center;
          background-repeat: no-repeat;
          background-size: cover;
      }

      .login-box{
        background-color:rgba(0, 0, 0, 0.5);
        padding: 20px;
        border-radius: 10px;
      }

  </style>
</head>
<body class="bg">
<div class="login-box">
  <div class="login-logo">
    <a href="" style="color:white"><img src="{{url('/')}}/public/images/logo2.png">
</a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="login-box-body">
      <p class="login-box-msg">Sign in to start your session</p>

      <form action="{{ route('login') }}" method="post">
        {{ csrf_field() }}

        <div class="form-group has-feedback {{ $errors->has('email') ? ' has-error' : '' }}">
          <input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}" required>
          <span class="fa fa-envelope form-control-feedback"></span>
          @if ($errors->has('email'))
              <span class="help-block">
                  <strong>{{ $errors->first('email') }}</strong>
              </span>
          @endif
        </div>
        <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
          <input type="password" class="form-control" placeholder="Password" name="password" required="">
          <span class="fa fa-lock form-control-feedback"></span>
          @if ($errors->has('password'))
              <span class="help-block">
                  <strong>{{ $errors->first('password') }}</strong>
              </span>
          @endif
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="checkbox icheck">
              <label>
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-md-6">
            <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>
      <p class="mb-1 text-center">
        <a href="#">I forgot my password</a>
      </p>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="{{url('/')}}/public/LTE/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="{{url('/')}}/public/LTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- iCheck -->
<script src="{{url('/')}}/public/LTE/plugins/iCheck/icheck.min.js"></script>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass   : 'iradio_square-blue',
      increaseArea : '20%' // optional
    })
  })
</script>
</body>
</html>
