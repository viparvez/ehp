@extends('layouts.ehplayout')
  
@section('header-resources')
  <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- daterange picker -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/bootstrap-daterangepicker/daterangepicker.css">
  <!-- bootstrap datepicker -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/plugins/iCheck/all.css">
  <!-- Bootstrap Color Picker -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css">
  <!-- Bootstrap time Picker -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/plugins/timepicker/bootstrap-timepicker.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/select2/dist/css/select2.min.css">
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
@endsection

@section('content')
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Profile
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Profile</a></li>
        <li><a href="#">{{Auth::user()->name}}</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="box box-default">
          <div class="box-body">

              <div class="col-md-12 col-sm-12" style="padding-top: 50px">
                <form name="" id="image-update" method="POST" action="{{route('profile.image-update', Auth::user()->id)}}" enctype="multipart/form-data">
                  
                  <div class="col-md-3 col-sm-3">
                    <img src="{{Auth::user()->img_url}}" id="dp" class="img img-bordered img-square" width="200px" height="200px">
                    <hr>
                    {{csrf_field()}}
                    <div class="form-group">
                      <label>Change Image</label>
                      <input type='file' name='inputfile' id='inputfile'>
                    </div>
                  </div>

                </form>

                <div class="col-md-3 col-sm-3 col-md-offset-1 col-sm-offset-1">
                  
                  <div class="form-group">
                    <label>Name:</label>
                    <a href="#"><h4>{{$user->name}}</h4></a>
                  </div>

                  <div class="form-group">
                    <label>Email:</label>
                    <a href="#"><h4>{{$user->email}}</h4></a>
                  </div>

                  <div class="form-group">
                    <label>Phone:</label>
                    <a href="#"><h4>{{$user->phone}}</h4></a>
                  </div>
                  
                </div>


                <div class="col-md-3 col-sm-3 col-md-offset-1 col-sm-offset-1">

                  <div class="form-group">
                    <label>Company:</label>
                    <a href="#"><h4>{{$user->company}}</h4></a>
                  </div>

                  <div class="form-group">
                    <label>Project:</label>
                    <a href="#"><h4>{{$user->project}}</h4></a>
                  </div>

                  <div class="form-group">
                    <label>Designation:</label>
                    <a href="#"><h4>{{$user->designation}}</h4></a>
                  </div>

                </div>

              </div>

              <div class="col-md-3 col-sm-3 col-md-offset-4 col-md-offset-4">
                <button class="btn btn-block btn-success btn-flat" data-toggle="modal" data-target="#myModal">Edit Profile</button> <br>
              </div>

              <div class="col-md-3 col-sm-3">
                <button class="btn btn-block btn-warning btn-flat" data-toggle="modal" data-target="#changePass">Change Password</button> <br>
              </div>
              
          </div>
        </div>
      </div>
    </section>
    <!-- /.content -->
  </div>


  <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title text-center">{{$user->name}}</h4>
        </div>
        <div class="modal-body">
          
          <div class="alert alert-danger print-error-msg" style="display:none">
              <ul></ul>
          </div>

          <form id="profile" action="{{route('profile.store')}}" method="POST">

            {{csrf_field()}}
            <div class="form-group">
             <label>Name <code>*</code></label>
             <input type="text" name="name" placeholder="Name" class="form-control" value="{{$user->name}}" required>
            </div>


            <div class="form-group">
             <label>Email</label>
             <input type="email" name="email" placeholder="Email" class="form-control" value="{{$user->email}}" readonly>
            </div>

            <div class="form-group">
             <label>Phone <code>*</code></label>
             <input type="text" name="phone" placeholder="Phone" class="form-control" value="{{$user->phone}}" required>
            </div>

            <div class="form-group">
             <label>Company</label>
             <input type="text" name="company" placeholder="Company" class="form-control" value="{{$user->company}}" readonly>
            </div>

            <div class="form-group">
             <label>Project</label>
             <input type="text" name="project" placeholder="Project" class="form-control" value="{{$user->project}}" readonly>
            </div>

            <div class="form-group">
             <label>Designation</label>
             <input type="text" name="designation" placeholder="Designation" class="form-control" value="{{$user->designation}}" readonly>
            </div>

            <button class="btn btn-block btn-primary btn-sm" id="submit" type="submit">SUBMIT</button>
            <button class="btn btn-block btn-primary btn-sm" id="loading" style="display: none" disabled="">Working...</button>

          </form>

        </div>
      </div>
    </div>
  </div>


  <div class="modal fade" id="changePass" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title text-center">Change Password</h4>
        </div>
        <div class="modal-body">
          
          <div class="alert alert-danger print-error-msg" style="display:none">
              <ul></ul>
          </div>

          <form id="changep" action="{{route('profile.changePass', Auth::user()->id)}}" method="POST">

            {{csrf_field()}}

            <div class="form-group">
             <label>New Password: <code>*</code></label>
             <input type="password" name="password" placeholder="Enter new password" class="form-control">
            </div>

            <div class="form-group">
             <label>Re-enter Password: <code>*</code></label>
             <input type="password" name="password_confirmation" placeholder="Re-enter new password" class="form-control">
            </div>

            <button class="btn btn-block btn-primary btn-sm" id="submit_1" type="submit">SUBMIT</button>
            <button class="btn btn-block btn-primary btn-sm" id="loading_1" style="display: none" disabled="">Working...</button>

          </form>

        </div>
      </div>
    </div>
  </div>

@endsection


@section('footer-resources')


<!-- Select2 -->
<script src="{{url('/')}}/public/LTE/bower_components/select2/dist/js/select2.full.min.js"></script>
<!-- InputMask -->
<script src="{{url('/')}}/public/LTE/plugins/input-mask/jquery.inputmask.js"></script>
<script src="{{url('/')}}/public/LTE/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="{{url('/')}}/public/LTE/plugins/input-mask/jquery.inputmask.extensions.js"></script>
<!-- date-range-picker -->
<script src="{{url('/')}}/public/LTE/bower_components/moment/min/moment.min.js"></script>
<script src="{{url('/')}}/public/LTE/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- bootstrap datepicker -->
<script src="{{url('/')}}/public/LTE/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<!-- bootstrap color picker -->
<script src="{{url('/')}}/public/LTE/bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
<!-- bootstrap time picker -->
<script src="{{url('/')}}/public/LTE/plugins/timepicker/bootstrap-timepicker.min.js"></script>

<script src="{{url('/')}}/public/LTE/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="{{url('/')}}/public/LTE/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>


<script type="text/javascript">
  
  $(document).ready(function() {
      $('#inputfile').change(function(){

          var _url = $("#image-update").attr("action");

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });


        var formData = new FormData($('#image-update')[0]);

          $.ajax({

              url: _url,
              type:'POST',
              dataType:"json",
              processData: false,
              contentType: false,
              data:formData,
              success: function(data) {

                  if($.isEmptyObject(data.error)){

                    $('#dp').attr("src",data.success);
                    $('#nav-dp').attr("src",data.success);
                    $('#left-dp').attr("src",data.success);
                    $('#top-dp').attr("src",data.success);

                  }else{

                    console.log(data.error);

                  }

              }

          });
      });

      $(document).ajaxStart(function () {
          $('#dp').css("opacity","0.2");
          $("#submit").hide();
          $("#loading").show();
          $("#submit_1").hide();
          $("#loading_1").show();
      }).ajaxStop(function () {
          $('#dp').css("opacity","1.0");
          $("#submit").show();
          $("#loading").hide();
          $("#submit_1").show();
          $("#loading_1").hide();
      });


      //Submit Edit

      $("#submit").click(function(e){

        e.preventDefault();
        var _url = $("#profile").attr("action");
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var _data = $("#profile").serialize();
        var formData = new FormData($('#profile')[0]);


          $.ajax({

              url: _url,
              type:'POST',
              dataType:"json",
              processData: false,
              contentType: false,
              data:formData,

              success: function(data) {

                  if($.isEmptyObject(data.error)){

                    swal({
                      title: "Updated!",
                      text: "Profile Updated",
                      icon: "success",
                      button: false,
                      timer: 2000,
                      showCancelButton: false,
                      showConfirmButton: false
                    }).then(
                      function () {
                        window.location.reload(true);
                      },
                    );

                  }else{

                    printErrorMsg(data.error);

                  }
              }
          });
      });


      $("#submit_1").click(function(e){

        e.preventDefault();
        var _url = $("#changep").attr("action");
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var _data = $("#changep").serialize();
        var formData = new FormData($('#changep')[0]);


          $.ajax({

              url: _url,
              type:'POST',
              dataType:"json",
              processData: false,
              contentType: false,
              data:formData,

              success: function(data) {

                  if($.isEmptyObject(data.error)){

                    swal({
                      title: "Updated!",
                      text: "Password Changed",
                      icon: "success",
                      button: false,
                      timer: 2000,
                      showCancelButton: false,
                      showConfirmButton: false
                    }).then(
                      function () {
                        window.location.reload(true);
                      },
                    );

                  }else{

                    printErrorMsg(data.error);

                  }
              }
          });
      });


      function printErrorMsg (msg) {
        $(".print-error-msg").find("ul").html('');
        $(".print-error-msg").css('display','block');
        $.each( msg, function( key, value ) {
          $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
        });

      }

  });

</script>

@endsection
