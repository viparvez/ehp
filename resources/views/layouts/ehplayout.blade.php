<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ config('app.name', 'EHMP') }}</title>
  <meta http-equiv="refresh" content="7200;url={{route('sess_out')}}" />
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/dist/css/skins/_all-skins.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">



  <style type="text/css">
    .details-view tr td:nth-child(odd) {
      color: #3C8DBC;
      padding-right: 20px; 
    }
    .details-view tr td:nth-child(even) {
      text-align: right;
    }

    input[type="checkbox"]:checked {
      /*box-shadow: 0 0 0 1px blue;*/
    }

    .modal { overflow: auto !important; }


    @import url('https://fonts.googleapis.com/css?family=Roboto+Condensed:700&subset=cyrillic');
    
    #save > input{
      display:none;  
    }
    .block{
      width:200px;
      position:relative;
      clear:both;
      margin:0 0 25px;
      float: left;
    }
    #save > span{
      text-transform:uppercase;
      font-family:'Roboto Condensed', sans-serif;
      font-weight:bold;
      letter-spacing:1px;
      font-size:15px;
      float:right;
      width:85px;
      margin:16px 0 0;
    }
    #save > .wrap{
      width:200px;
      position: absolute;
      left:50%;
      top:50%;
      transform:translate(-50%,-50%);
      padding:30px 30px 5px;
    }
    #save > label{
      width:100px;
      height:50px;
      box-sizing:border-box;
      border:3px solid;
      float:left;
      border-radius:100px;
      position:relative;
      cursor:pointer;
      transition:.3s ease;
    }
    #save > input[type=checkbox]:checked + label{
      background:#55e868;
    }
    #save > input[type=checkbox]:checked + label:before{
      left:50px;
    }
    #save > label:before{
      transition:.3s ease;
      content:'';
      width:40px;
      height:40px;
      position:absolute;
      background:white;
      left:2px;
      top:2px;
      box-sizing:border-box;
      border:3px solid;
      color:black;
      border-radius:100px;
    }


    #ck-button label input {
       margin-right:100px;
    }


    #ck-button {
        margin:5px;
        background-color:#EFEFEF;
        border-radius:4px;
        border:1px solid #D0D0D0;
        overflow:auto;
        float:left;
        height: 50px;
    }

    #ck-button label {
        float:left;
        width:auto;
        padding-top: 12px;
    }

    #ck-button label span {
        text-align:center;
        padding:12px 10px;
        font-size: 16px;
        height: 40px;
    }

    #ck-button label input {
        position:absolute;
    }

    #ck-button input:checked + span{
        background-color:#3C763D;
        color:#fff;
    }

  </style>

  @yield('header-resources')

</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  
  @include('includes/nav')

  @include('includes/left')

  @yield('content')

  @include('includes/footer')

</div>
<!-- ./wrapper -->

<!-- jQuery 3 -->
<!-- <script src="public/LTE/bower_components/jquery/dist/jquery.min.js"></script> -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="{{url('/')}}/public/LTE/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- SlimScroll -->
<script src="{{url('/')}}/public/LTE/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- iCheck 1.0.1 -->
<script src="{{url('/')}}/public/LTE/plugins/iCheck/icheck.min.js"></script>
<!-- FastClick -->
<script src="{{url('/')}}/public/LTE/bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="{{url('/')}}/public/LTE/dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{url('/')}}/public/LTE/dist/js/demo.js"></script>

<script src="{{url('/')}}/public/js/sweetalert.js"></script>

<script type="text/javascript">
  
  var csrfToken = $('[name="csrf_token"]').attr('content');

  setInterval(refreshToken, 1800000); 

  function refreshToken(){
    $.get('refresh-csrf').done(function(data){
        csrfToken = data; 
    });
  }

  setInterval(refreshToken, 1800000); 

</script>

@if (notify()->ready())   
 <script>
  swal({
    title: "{!! notify()->message() !!}",
    text: "{!! notify()->option('text') !!}",
    type: "{{ notify()->type() }}",
    @if (notify()->option('timer'))
        timer: {{ notify()->option('timer') }},
        showConfirmButton: false
    @endif
  });
 </script>
@endif

@yield('footer-resources')
  
</body>
</html>