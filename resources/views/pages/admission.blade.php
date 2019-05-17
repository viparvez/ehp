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

  <link rel="stylesheet" type="text/css" href="public/style.css">

  <link rel="stylesheet" href="public/LTE/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
@endsection

@section('content')
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Client Admission
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Client Management</a></li>
        <li><a href="#">Client Admission</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      
      <!-- <div class="col-md-2">
        <button class="btn btn-block btn-success btn-flat">NEW</button>
      </div> -->

      <div class="row">
        <div class="box box-default">
          <div class="box-body">
            @ability('Admin','Create-Admission')
              <div class="col-md-2 col-sm-2 col-md-offset-10 col-sm-offset-10">
                <button class="btn btn-block btn-success btn-flat" data-toggle="modal" data-target="#myModal">NEW</button> <br>
              </div> 
            @endability
            <table class="table table-bordered table-striped" id="example1">
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Code</th>
                  <th>Client Name</th>
                  <th>Building</th>
                  <th>Floor</th>
                  <th>Apartment</th>
                  <th>Status</th>
                  <th width="100">Movein Date</th>
                  <th width="100">Moveout Date</th>
                  <th>Days Staying</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($admissions as $key => $value)
                  <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{$value->admissionid}}</td>
                    <td>{{$value->Client->fname}} {{$value->Client->lname}}
                			@if($value->client->Precondition->name == 'Transferred')
                			<button class="btn bg-aqua btn-flat btn-xs">TRANSF</button>
                			@endif
             		    </td>

                    <td>{{$value->Apartment->Floor->Facility->code}} - {{$value->Apartment->Floor->Facility->name}}</td>
                    <td>{{$value->Apartment->Floor->name}}</td>
                    <td>{{$value->Apartment->name}}</td>
                    <td>
                      @if($value->moveoutdate == null)
                        <button class="btn bg-green btn-flat btn-xs">Admitted</button>
                      @else
                        <button class="btn bg-maroon btn-flat btn-xs">Discharged</button>
                      @endif
                    </td>
                    <td>{{date('m-d-Y',strtotime($value->moveindate))}}</td>
                    <td>
                      @if(!empty($value->moveoutdate))
                        {{date('m-d-Y',strtotime($value->moveoutdate))}}
                      @else
                      @endif
                    </td>
                    <td>
                      @if($value->moveoutdate == null)
                        @php
                          $date = date_create($value->moveindate);
                          $now = date_create(date("Y-m-d"));
                          $diff = date_diff($date,$now);
                        @endphp
                      @else
                        @php
                          $date = date_create($value->moveindate);
                          $now = date_create($value->moveoutdate);
                          $diff = date_diff($date,$now);
                        @endphp
                      @endif
                        {{$diff->format("%a days")}}
                    </td>
                    <td style="min-width: 150px">
                      
                        <a class="btn btn-xs btn-info btn-flat" data-toggle="modal" id="showPreview" onclick="show({{$value->id}})">
                          <span class="fa fa-eye"></span>
                        </a>

                      @if(empty($value->moveoutdate))
                        <a class="btn btn-xs btn-warning btn-flat" data-toggle="modal" onclick="discharge({{$value->id}})">
                          Discharge 
                        </a>
                      @else
                      @endif

                        <button class="btn btn-xs btn-danger btn-flat" data-toggle="modal" id="showPreview" onclick="deletionAction({{$value->id}})">
                            <span class="fa fa-trash"></span>
                        </button>
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
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title text-center">NEW ADMISSION</h4>
        </div>
        <div class="modal-body">

          <div class="alert alert-danger print-error-msg" style="display:none">
              <ul></ul>
          </div>

          <form id="admissions" action="{{route('admissions.store')}}" method="POST">

            {{csrf_field()}}
              
            <div class="form-group">
             <label>Client <code>*</code></label>
             <input type="text" name="client" id="client" placeholder="Client Code/Name/SSN/Email" class="form-control" autocomplete="off" required>
             <div id="client_ops" style="display:none;">
                <ul id="testul">
                </ul>
            </div>
            <input type="hidden" name="client_id" id="client_id">
            </div>

            <div class="form-group">
              <label>Move in date: <code>*</code></label>
              <div class="input-group date moveindate">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="text" class="form-control pull-right" id="datepicker" name="moveindate">
              </div>
            </div>


            <div class="form-group">
             <label>Building <code>*</code></label>
             <select name="building" id="building" class="form-control">
                <option value="">SELECT</option>
               @foreach($buildings as $key => $value)
                <option value="{{$value->id}}">{{$value->code}} - {{$value->name}}</option>
               @endforeach
             </select>
            </div>

            <div class="form-group">
             <label>Apartment <code>*</code></label>
             <select name="apartment_id" id="apartment_id" class="form-control">
               <option value="">SELECT</option>
             </select>
            </div>

            <button class="btn btn-block btn-primary btn-sm" id="submit" type="submit">SUBMIT</button>
            <button class="btn btn-block btn-primary btn-sm" id="loading" style="display: none" disabled="">Working...</button>

          </form>

        </div>
      </div>
    </div>
  </div>



  <div class="modal fade" id="preview" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="show">
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



  <!-- Discharge Form -->

  <div class="modal fade" id="dischargeForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="show_dis">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

          <div class="alert alert-danger print-error-msg" id="dis-error" style="display:none">
              <ul></ul>
          </div>
         
          <div class="text-center">
            <img src="public/images/loading.gif" id="loadinggifdis">
          </div>
          
          <div id="dischargeContent">
            
          </div>
          
        </div>
      </div>
    </div>
  </div>

@endsection


@section('footer-resources')

<script type="text/javascript">
  
  $(document).ready(function() {

      $("#submit").click(function(e){

        e.preventDefault();

        var _url = $("#admissions").attr("action");


        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var _data = $("#admissions").serialize();

          $.ajax({

              url: _url,

              type:'POST',

              dataType:"json",

              data:_data,

              success: function(data) {

                  if($.isEmptyObject(data.error)){
                    swal({
                      title: "Created!",
                      text: "Client Admitted",
                      icon: "success",
                      button: false,
                      timer: 2000,
                      showCancelButton: false,
                      showConfirmButton: false
                    }).then(
                      function () {
                        window.location.reload();
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

  $("#close_dis").click(function(){
    $("#dischargeForm").find("#dischargeContent").html("");
    $('#dischargeForm').modal('hide');
    $("#loadinggifdis").show();
  });

  function show(id) {
    $('#preview').modal('show');

    var showUrl = "{{url('/')}}/admissions/"+id;
     
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


  function discharge(id) {
    $('#dischargeForm').modal('show');

    var showUrl = "{{url('/')}}/admissions/discharge/"+id;
     
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $("#loadinggifdis").hide();
        $("#dischargeForm").find("#dischargeContent").html(this.responseText);
        $('#datepicker1').datepicker({
          format: 'mm-dd-yyyy',
          autoclose: true,
        });
      }
    };
    xhttp.open("GET", showUrl, true);
    xhttp.send();
  }


  $("#client").keydown(function(){

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var query = $("#client").val();
    
    if (query.length > 1) {
        $.ajax({
          url:"{{url('/')}}/client/ajaxsearch",
          method: "POST",
          dataType: 'text',
          data: {
            input: query
          },
          success: function(data) {
            var response = jQuery.parseJSON(data);
            printClient(response);
            console.log(response);

          },
        });
    }

  });


  $(document).on("submit", "#disForm",function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var _data = $("#disForm").serialize();

    var formData = new FormData($('#disForm')[0]);

    $.ajax({
      url:"{{url('/')}}/admissions/discharge",
      method: "POST",
      dataType: 'json',
      processData: false,
      contentType: false,
      data: formData,
      beforeSend: function() { 
         $("#submit_discharge").hide();
         $("#loading_discharge").show();
      },
      success: function(data) {
        if($.isEmptyObject(data.error)){
          swal({
            title: "Updated!",
            text: "Discharge was successful!",
            icon: "success",
            button: false,
            timer: 2000,
            showCancelButton: false,
            showConfirmButton: false
          }).then(
            function () {
              location.reload(true);
            },
          );

        }else{
          
          printErrorMsg(data.error);

          $("#loading_discharge").hide();
          $("#submit_discharge").show();

        }

      },
    });

  });


  $("#showcontent").on('click', '#submitEdit',function(e){

    e.preventDefault();

    var _url = $("#deleteAdmission").attr("action");

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var _data = $("#deleteAdmission").serialize();

      $.ajax({

          url: _url,

          type:'POST',

          dataType:"json",

          data:_data,

          success: function(data) {

              if($.isEmptyObject(data.error)){
                console.log(data);
                swal({
                  title: "Deleted!",
                  text: "Admission Deleted",
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
                console.log(data.error);
                printErrorMsg(data.error);

              }

          }

      });

  });

  function printErrorMsg(msg) {
    $("#error_messages").find("ul").html('');
    $("#error_messages").css('display','block');
    $.each( msg, function( key, value ) {
      $("#error_messages").find("ul").append('<li>'+value+'</li>');
    });
  }

  $("#testul").on('click', 'li', function(){
    var client = $(this).val();
    var content = $(this).text();

    if(content != 'No data found'){
      $("#client_ops").css('display','none');
      $("#client").val(content);
      $("#client").attr('readonly', 'true');
      $("#client_ops").find("ul").html('');
      $("#client_id").val(client);
    }
    
  });


  $("#client").dblclick(function(){
      $("#client").val('');
      $("#client").removeAttr("readonly");
      $("#client_id").val('');
  }); 


  function printClient (msg) {

    $("#client_ops").find("ul").html('');
    $("#client_ops").css('display','block');
    
    if (msg.error) {
      $("#client_ops").find("ul").append('<li>No data found</li>');
    } else {
      $.each( msg, function( key, value ) {
        var li = (value.fname + ' ' + value.lname +'(Code:'+value.code+')'+'(SSN:' + value.ssn +')' );
        $("#client_ops").find("ul").append('<li value='+value.id+'>'+li+'</li>');
      });
    }
    
  }

  function deletionAction(id) {
    $('#preview').modal('show');

    var showUrl = "{{url('/')}}/admissions/"+id+"/deletion";
     
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

  $('#building').on('change', function(e){

    var building = e.target.value;
    var moveindate = $("#datepicker").val();

    //if(Date.parse(moveindate)) {
      $.get( "{{url('/')}}/facility/" + building + "/apartment/"+moveindate, function( data ) {

        $('#apartment_id').empty();
        $('#apartment_id').append("<option value=''>Select</option>");

        $.each(data, function(index, Object){
          $('#apartment_id').append('<option value="'+Object.id+'">'+Object.name+'</option>');
        });
      }); 
    /*} else {
      alert('Select movein date first.');
    }
     */
  });

</script>

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

    $('#datepicker1').datepicker({
      format: 'mm-dd-yyyy',
      autoclose: true
    })

  })

  $(function () {
    $('#example1').DataTable({
      'paging'      : true,
      'lengthChange': true,
      'searching'   : true,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })
</script>

@endsection
