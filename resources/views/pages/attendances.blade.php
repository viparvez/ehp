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

  <script type="text/javascript">
    function toggle(source) {
      checkboxes = document.getElementsByName('foo');
      for(var i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = source.checked;
        var id = checkboxes[i].getAttribute('id');
        var inputId = 'attendance'+id;
        
        if (inputId != 'attendanceskip' && checkboxes[i].checked == true) {
          document.getElementById(inputId).value = '1';  
        } else if(inputId != 'attendanceskip' && checkboxes[i].checked == false) {
          document.getElementById(inputId).value = '0';           }

      }
    }
  </script>

  <style>
  /* The container */
  .container {
      display: block;
      position: relative;
      padding-left: 35px;
      margin-bottom: 12px;
      cursor: pointer;
      font-size: 16px;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
  }

  /* Hide the browser's default checkbox */
  .container input {
      position: absolute;
      opacity: 0;
      cursor: pointer;
  }

  /* Create a custom checkbox */
  .checkmark {
      position: absolute;
      top: 0;
      left: 0;
      height: 25px;
      width: 25px;
      background-color: #eee;
  }

  /* On mouse-over, add a grey background color */
  .container:hover input ~ .checkmark {
      background-color: #ccc;
  }

  /* When the checkbox is checked, add a blue background */
  .container input:checked ~ .checkmark {
      background-color: #2196F3;
  }

  /* Create the checkmark/indicator (hidden when not checked) */
  .checkmark:after {
      content: "";
      position: absolute;
      display: none;
  }

  /* Show the checkmark when checked */
  .container input:checked ~ .checkmark:after {
      display: block;
  }

  /* Style the checkmark/indicator */
  .container .checkmark:after {
      left: 9px;
      top: 5px;
      width: 5px;
      height: 10px;
      border: solid white;
      border-width: 0 3px 3px 0;
      -webkit-transform: rotate(45deg);
      -ms-transform: rotate(45deg);
      transform: rotate(45deg);
  }
  </style>

@endsection

@section('content')
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Attendance
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Attendance</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="box box-default">
          <div class="box-body">

            <div class="col-md-12 col-xs-12">
              <form name="search" id="search" action="{{route('attendances.processForm')}}" method="POST">
                {{csrf_field()}}
                <div class="col-md-4 col-xs-12">
                  <div class="form-group">
                    <label>Facility/Building:</label>
                    <select class="form-control" name="facility_id" required="">
                      <option value="">SELECT</option>
                      @foreach($facilities as $facility)
                        @php
                          $selected = '';
                          if(!empty($apartments)) {
                            if($facility->id == $apartments[0]['facility_id']) {
                              $selected = 'selected';
                            }
                          }
                        @endphp
                        <option value="{{$facility->id}}" {{$selected}}>{{$facility->code}} - {{$facility->name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="col-md-4 col-xs-12">
                  <div class="form-group">
                    <label>date:</label>
                    <div class="input-group date moveindate">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right" id="datepicker" name="date" readonly="" @if(!empty($date)) value="{{date('m-d-Y',strtotime($date))}}" @else value="{{date('m-d-Y')}}" @endif  required="">
                    </div>
                  </div>
                </div>

                <div class="col-md-4 col-xs-12">
                  <label style="color: white">Search</label>
                  <button class="form-control btn btn-sm btn-primary">SEARCH</button>
                </div>

              </form>

            </div>

            <br><br><br><br>
            
            <hr><br>
            @if(\Request::route()->getName() == 'attendances.showForm')
              @if(!empty($apartments))
              <form method="POST" id="attendance" action="{{route('attendances.store')}}">

                <div class="col-md-8 col-md-offset-2 col-xs-12" style="min-height: 300px">
                  <h4 class="text-center">Facility/Building: <a href="#">{{$apartments[0]['facility_name']}} <u>({{$apartments[0]['facility_code']}})</u></a></h4>
                  <hr>

                  @if(!empty($fileHas))
                    <div class="col-md-6 col-xs-12 col-md-offset-4">
                      <h3><a href="{{url('/')}}{{$fileHas[0]->file}}" download>DOWNLOAD DOCUMENT</a></h3>
                    </div>
                  @else

                  @endif
                  
                    {{csrf_field()}}
                    <input type="hidden" name="facility_id" value="{{$apartments[0]['facility_id']}}">
                    <input type="hidden" name="date" value="{{$date}}">

                    <div class="col-md-12 col-xs-12" style="padding-bottom: 20px">
                      <label class="container">Toggle All
                        <input type="checkbox" name="foo" id="skip" onclick="toggle(this)">
                        <span class="checkmark"></span>
                      </label>
                    </div><br>
                    @foreach($apartments as $ap)
                      <div class="col-md-4 col-xs-12" style="padding-bottom: 20px">
                        <label class="container">{{$ap['name']}}
                          <input type="checkbox" name="foo" id="{{$ap['id']}}" onclick="attendance('{{$ap['id']}}')">
                          <input type="hidden" name="attendance[{{$ap['id']}}]" value="0" id="attendance{{$ap['id']}}">
                          <span class="checkmark"></span>
                        </label>
                      </div>
                      
                    @endforeach  
                </div>

                <div class="col-md-4 col-xs-12 col-md-offset-4">
                  <div class="form-group">
                    <label>Comments</label>
                    <textarea class="form-control" name="comment"></textarea>
                  </div>
                </div>

                <div class="col-md-6 col-xs-12 col-md-offset-4">
                  <div class="form-group">
                    <label>Document</label>
                    <input type="file" name="document" id="document" >
                  </div>
                </div>

                  <div class="col-md-4 col-md-offset-4 col-xs-12">
                    <div class="form-group">
                      <button class="btn btn-success form-control" id="submit_attendance" hidden>SAVE</button>
                    </div>
                  </div>

                </form>

              @else
                <h3 class="text-center" style="color: red">No any occupied units in this facility.</h3>
              @endif
            @else
            @endif
          </div>
        </div>
      </div>
    </section>
    <!-- /.content -->
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

      $("#submit_attendance").click(function(e){

        e.preventDefault();

        var _url = $("#attendance").attr("action");


        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var _data = $("#attendance").serialize();

          var formData = new FormData($('#attendance')[0]);

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
                      title: "Added",
                      text: "Attendance data inserted.",
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
          $("#loadingEdit").show();
          $("#submitEdit").hide();
      }).ajaxStop(function () {
          $("#loading").hide();
          $("#submit").show();
          $("#loadingEdit").hide();
          $("#submitEdit").show();
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



  function printUpdateError(msg) {
    $("#error_messages").find("ul").html('');
    $("#error_messages").css('display','block');
    $.each( msg, function( key, value ) {
      $("#error_messages").find("ul").append('<li>'+value+'</li>');
    });
  }

  function attendance(id) {
    if(document.getElementById(id).checked) {
      document.getElementById("attendance"+id).value = '1';
    }  else {
      document.getElementById("attendance"+id).value = '0';
    }
  }

</script>

<!-- Page script -->
<script type="text/javascript">

  $(function () {
    //Date picker
    $('#datepicker').datepicker({
      format: 'mm-dd-yyyy',
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
      'ordering'    : false,
      'info'        : true,
      'autoWidth'   : false
    })
  })

</script>

@endsection