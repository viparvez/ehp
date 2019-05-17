@extends('layouts.ehplayout')
  
@section('header-resources')
  <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- daterange picker -->
  <link rel="stylesheet" href="public/LTE/bower_components/bootstrap-daterangepicker/daterangepicker.css">
  <!-- bootstrap datepicker -->
  <link rel="stylesheet" href="public/LTE/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="public/LTE/plugins/iCheck/all.css">
  <!-- Bootstrap Color Picker -->
  <link rel="stylesheet" href="public/LTE/bower_components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css">
  <!-- Bootstrap time Picker -->
  <link rel="stylesheet" href="public/LTE/plugins/timepicker/bootstrap-timepicker.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="public/LTE/bower_components/select2/dist/css/select2.min.css">

  <link rel="stylesheet" href="public/LTE/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">

  <style type="text/css">

    -moz-appearance:none;
    -webkit-appearance:none;
    -o-appearance:none;

    .modal { overflow: auto !important; }

  </style>

@endsection

@section('content')
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Users
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">User Management</a></li>
        <li><a href="#">Users</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <div class="box box-default">
          <div class="box-body">
            @ability('Admin','Create-User')
            <div class="col-md-2 col-sm-2 col-md-offset-10 col-sm-offset-10">
              <button class="btn btn-block btn-success btn-flat" data-toggle="modal" data-target="#myModal">NEW</button> <br>
            </div> 
            @endability
            <table id="example1" class="table table-bordered table-striped">
              <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Name</th>
                <th>Designation</th>
                <th>Company & Project</th>
                <th>Email</th>
                <th>Created On</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
              </thead>
              <tbody>
                @foreach($users as $key => $user)
                  <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{$user->name}}</td>
                    <td>{{$user->designation}}</td>
                    <td>{{$user->company}} , {{$user->project}}</td>
                    <td>{{$user->email}}</td>
                    <td>{{$user->created_at->format('m-d-Y')}}</td>
                    <td>
                      @if($user->active == '1')
                        <button class="btn bg-olive btn-flat btn-xs">Active</button>
                      @else
                        <button class="btn bg-maroon btn-flat btn-xs">Inctive</button>
                      @endif
                    </td>
                    <td>
                      @ability('Admin','View-User-Details')
                      <button class="btn btn-xs btn-info btn-flat" data-toggle="modal" id="showPreview" onclick="show({{$user->id}})">
                        <span class="fa fa-eye"></span>
                      </button>
                      @endability
                      @ability('Admin','User-Activation')
                      <button class="btn btn-xs btn-danger btn-flat" data-toggle="modal" id="showPreview" onclick="activationAction({{$user->id}})">
                        <span class="fa fa-power-off"></span>
                      </button>
                      @endability
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
    <!-- /.content -->
  </div>


  <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title text-center">NEW USER</h4>
        </div>
        <div class="modal-body">
          
          <div class="alert alert-danger print-error-msg" style="display:none">
              <ul></ul>
          </div>

          <form id="user" action="{{route('users.store')}}" method="POST" enctype="multipart/form-data">

            {{csrf_field()}}

            <div class="col-sm-12 col-md-6 col-lg-6 col-xs-12">
              
              <div class="form-group">
               <label>Name <code>*</code></label>
               <input type="text" name="name" placeholder="Name" class="form-control" required>
              </div>

              <div class="form-group">
                <label>Email <code>*</code></label>
                <input type="email" name="email" class="form-control" placeholder="Email" required>
              </div>

              <div class="form-group">
               <label>Company <code>*</code></label>
               <input type="text" name="company" placeholder="Company" class="form-control" required>
              </div>

              <div class="form-group">
               <label>Project </label>
               <input type="text" name="project" placeholder="Project" class="form-control" required>
              </div>

            </div>

            <div class="col-sm-12 col-md-6 col-lg-6 col-xs-12">

              <div class="form-group">
               <label>Designation <code>*</code></label>
               <input type="text" name="designation" placeholder="Designation" class="form-control" required>
              </div>

              <div class="form-group">
               <label>Phone <code>*</code></label>
               <input type="text" name="phone" placeholder="Phone" class="form-control" data-inputmask='"mask": "(999) 999-9999"' data-mask="" required>
              </div>

              <div class="form-group">
                <label>Password <code>*</code></label>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
              </div>

              <div class="form-group">
                <label>Confirm Password <code>*</code></label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Password Retype" required>
              </div>

              <div class="form-group">
                <label>Image</label>
                <input type="file" name="img_url" id="img_url" >
              </div>
              
            </div>

            <div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">

              <fieldset class="scheduler-border">
                <legend>Roles: <code>*</code></legend>
                <div class="col-md-12 col-sm-12">
                @foreach($roles as $k => $v)
                  <div class="col-md-4 col-sm-12">
                    <input type="checkbox" name="role[]" value="{{$v->id}}"> {{$v->display_name}}</br>
                  </div>
                @endforeach
                </div>
              </fieldset><br>

              <fieldset class="scheduler-border">
                <legend>Has Access to Facilities: </legend>
                <div class="col-md-12 col-sm-12">
                @foreach($facilities as $k => $f)
                  <div class="col-md-3 col-sm-6">
                    <input type="checkbox" name="facility[]" value="{{$f->id}}"> {{$f->code}}</br>
                  </div>
                @endforeach
                </div>
              </fieldset><br>
            </div>

            </br>
            <button class="btn btn-block btn-primary btn-sm" id="submit" type="submit">SUBMIT</button>
            <button class="btn btn-block btn-primary btn-sm" id="loading" style="display: none" disabled="">Working...</button>

          </form>

        </div>
      </div>
    </div>
  </div>



  <div class="modal fade" id="preview" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeVdrShow">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
         
          <div class="text-center">
            <img src="public/images/loading.gif" id="loadinggif">
          </div>
          
          <div id="showcontent">
            
          </div>
          
        </div>
      </div>
    </div>
  </div>



  <div class="modal fade" id="preview_2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" id="modalsize_2" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeVdrShow">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
         
          <div class="text-center">
            <img src="public/images/loading.gif" id="loadinggif_2">
          </div>
          
          <div id="showcontent_2">
            
          </div>
          
        </div>
      </div>
    </div>
  </div>

@endsection


@section('footer-resources')

<!-- Select2 -->
<script src="public/LTE/bower_components/select2/dist/js/select2.full.min.js"></script>
<!-- InputMask -->
<script src="public/LTE/plugins/input-mask/jquery.inputmask.js"></script>
<script src="public/LTE/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="public/LTE/plugins/input-mask/jquery.inputmask.extensions.js"></script>
<!-- date-range-picker -->
<script src="public/LTE/bower_components/moment/min/moment.min.js"></script>
<script src="public/LTE/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- bootstrap datepicker -->
<script src="public/LTE/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<!-- bootstrap color picker -->
<script src="public/LTE/bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
<!-- bootstrap time picker -->
<script src="public/LTE/plugins/timepicker/bootstrap-timepicker.min.js"></script>

<script src="public/LTE/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="public/LTE/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

<script type="text/javascript">
  
  $(document).ready(function() {

      $("#submit").click(function(e){

        e.preventDefault();

        var _url = $("#user").attr("action");

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var _data = $("#user").serialize();

        var formData = new FormData($('#user')[0]);


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
                      title: "Created!",
                      text: "New user created",
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


      $(document).ajaxStart(function () {
          $("#loading").show();
          $("#submit").hide();
      }).ajaxStop(function () {
          $("#loading").hide();
          $("#submit").show();
      });


      function printErrorMsg (msg) {
        $(".print-error-msg").find("ul").html('');
        $(".print-error-msg").css('display','block');
        $.each( msg, function( key, value ) {
          $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
        });

      }

  });

  $("#show").click(function(){
    $("#preview").find("#showcontent").html("");
    $('#preview').modal('hide');
    $("#loadinggif").show();
  });

  function show(id) {
    $('#preview').modal('show');

    var showUrl = "{{url('/')}}/users/"+id;
     
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $("#loadinggif").hide();
        $("#preview").find("#showcontent").html(this.responseText);
      }
    };
    xhttp.open("GET", showUrl, true);
    xhttp.send();
  }


  function getEditForm(id) {

    $("#loadinggif").show();

    $("#preview").find("#showcontent").html("");

    var showUrl = "{{url('/')}}/users/"+id+"/edit";
     
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $("#loadinggif").hide();
        $("#preview").find("#showcontent").html(this.responseText);
      }
    };
    xhttp.open("GET", showUrl, true);
    xhttp.send();
  }


  function getPass(id) {

    $('#preview_2').modal('show');
    $("#loadinggif_2").show();

    $("#preview_2").find("#showcontent_2").html("");

    var showUrl = "{{url('/')}}/users/"+id+"/changepass";
     
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $("#loadinggif_2").hide();
        $("#preview_2").find("#showcontent_2").html(this.responseText);
      }
    };
    xhttp.open("GET", showUrl, true);
    xhttp.send();
  }


  $("#showcontent").on('click', '#submitEdit',function(e){

    e.preventDefault();

    var _url = $("#editUser").attr("action");

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var formData = new FormData($('#editUser')[0]);
    
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
                  text: "Data updated",
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
                
                printUpdateError(data.error);

              }

          }

      });

  });


  $("#showcontent_2").on('click', '#submitEdit',function(e){

    e.preventDefault();

    var _url = $("#edit_2").attr("action");

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });
    
    var formData = new FormData($('#edit_2')[0]);
    
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
                  text: "Data updated",
                  icon: "success",
                  button: false,
                  timer: 2000,
                  showCancelButton: false,
                  showConfirmButton: false
                }).then(
                  function () {
                    $('#preview_2').modal('hide');
                  },
                );

              }else{
                
                printUpdate2Error(data.error);

              }

          }

      });

  });

  function printUpdate2Error(msg) {
    $("#error_messages_2").find("ul").html('');
    $("#error_messages_2").css('display','block');
    $.each( msg, function( key, value ) {
      $("#error_messages_2").find("ul").append('<li>'+value+'</li>');
    });
  }

  function printUpdateError(msg) {
    $("#error_messages").find("ul").html('');
    $("#error_messages").css('display','block');
    $.each( msg, function( key, value ) {
      $("#error_messages").find("ul").append('<li>'+value+'</li>');
    });
  }


  function activationAction(id) {
    $('#preview').modal('show');

    var showUrl = "{{url('/')}}/users/"+id+"/activation";
     
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $("#loadinggif").hide();
        $("#preview").find("#showcontent").html(this.responseText);
      }
    };
    xhttp.open("GET", showUrl, true);
    xhttp.send();
  }

</script>

<!-- Page script -->
<script type="text/javascript">

  $(function () {
    //Date picker
    $('#datepicker').datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true
    })
    //Timepicker
    $('.timepicker').timepicker({
      showInputs: false
    })

    $('[data-mask]').inputmask();
    
  })

  $(function () {
    $('#example1').DataTable({
      'paging'      : true,
      'lengthChange': true,
      'searching'   : true,
      'ordering'    : [[ 2, 'asc' ]],
      'info'        : true,
      'autoWidth'   : false
    })
  })

</script>
@endsection
