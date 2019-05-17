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
        Units
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Location Management</a></li>
        <li><a href="#">Apartmenrs</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="box box-default">
          <div class="box-body">
            @ability('Admin','Create-Apartment')
              <div class="col-md-2 col-sm-2 col-md-offset-10 col-sm-offset-10">
                <button class="btn btn-block btn-success btn-flat" data-toggle="modal" data-target="#myModal">NEW</button> <br>
              </div> 
            @endability
            <table id="example1" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Code</th>
                  <th>Name</th>
                  <th>Floor</th>
                  <th>Building</th>
		  <th>Type</th>
                  <th>Vendor</th>
                  <th>Comment</th>
                  <th>Status</th>
                  <th>#Of Days Vacant</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
              @foreach($apartments as $key => $apartment)
                <tr>
                  <td>{{ $key + 1 }}</td>
                  <td>{{$apartment->code}}</td>
                  <td>{{$apartment->name}}</td>
                  <td>{{$apartment->floor_name}}</td>
                  <td>{{$apartment->facility_code}} - {{$apartment->facility_name}}</td>
                  <td>{{$apartment->facility_type}}</td>
                  <td>{{$apartment->vendor_name}}</td>
                  <td>{{$apartment->comment}}</td>
                  <td>
                    @if($apartment->active == '1')
                      @if($apartment->free == '1')
                        <button class="btn bg-green btn-flat btn-xs">Vacant</button>
                      @else
                        <button class="btn btn-xs btn-warning btn-flat">Currently Occupied</button>
                      @endif
                    @else
                      <button class="btn bg-maroon btn-flat btn-xs">Offline</button>
                    @endif
                  </td>
                  <td>
                    @if($apartment->free == '1')
                      @php
                        $date = date_create($apartment->vacantfrom);
                        $now = date_create(date("Y-m-d"));
                        $diff = date_diff($date,$now);
                      @endphp
                      {{$diff->format("%a days")}}
                    @else
                      0 days
                    @endif
                  </td>
                  <td>

                    <button class="btn btn-xs btn-info btn-flat" data-toggle="modal" id="showPreview" onclick="show({{$apartment->id}})">
                      <span class="fa fa-eye"></span>
                    </button>

                    <button class="btn btn-xs btn-danger btn-flat" data-toggle="modal" id="showPreview" onclick="activationAction({{$apartment->id}})">
                      <span class="fa fa-power-off"></span>
                    </button>
                    
                    <button class="btn btn-xs btn-danger btn-flat" data-toggle="modal" id="showPreview" onclick="deletionAction({{$apartment->id}})">
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
          <h4 class="modal-title text-center">NEW UNIT CREATION FORM</h4>
        </div>
        <div class="modal-body">
          
          <div class="alert alert-danger print-error-msg" style="display:none">
              <ul></ul>
          </div>

          <form id="apartments" action="{{route('apartments.store')}}" method="POST">

            {{csrf_field()}}

            <div class="form-group">
             <label>Name <code>*</code></label>
             <input type="text" name="name" placeholder="Name" class="form-control" required>
            </div>

            <div class="form-group">
             <label>Vendor</label>
             <select id="vendor" name="vendor" class="form-control" required>
               <option value="">SELECT</option>
              @foreach($vendors as $vendor)
               <option value="{{$vendor->id}}">{{$vendor->name}}</option>
              @endforeach
             </select>
            </div>

            <div class="form-group">
             <label>Building</label>
             <select id="facility_id" name="facility_id" class="form-control" required>
              
             </select>
            </div>

            <div class="form-group">
             <label>Floor <code>*</code></label>
             <select id="floor_id" name="floor_id" class="form-control" required>
              
             </select>
            </div>

            <div class="form-group">
              <label>Vacant From <code>*</code></label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="text" class="form-control pull-right" id="datepicker" name="vacantfrom" value="{{date('m-d-Y')}}">
              </div>
            </div>

            <div class="form-group">
             <label>Comment</label>
             <textarea id="comment" name="comment" class="form-control" required> </textarea>
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

      $("#submit").click(function(e){

        e.preventDefault();

        var _url = $("#apartments").attr("action");


        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var _data = $("#apartments").serialize();

          $.ajax({

              url: _url,

              type:'POST',

              dataType:"json",

              data:_data,

              success: function(data) {

                  if($.isEmptyObject(data.error)){
                    swal({
                      title: "Created!",
                      text: "New Apartment Added",
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

    var showUrl = "{{url('/')}}/apartments/"+id;
     
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

  function deletionAction(id) {
    $('#preview').modal('show');

    var showUrl = "{{url('/')}}/apartments/"+id+"/deletion";
     
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


  
  $('#vendor').on('change', function(e){

    var vendor = e.target.value;

    $.get( "{{url('/')}}/vendor/" + vendor + "/facility", function( data ) {

      $('#facility_id').empty();
      $('#facility_id').append("<option value=''>Select</option>");

      $.each(data, function(index, Object){
        $('#facility_id').append('<option value="'+Object.id+'">'+Object.code+' - '+Object.name+'</option>');
      });
    });  
  });

  $('#showcontent').on('change', '#vendorEdit', function(e){

    var vendor = e.target.value;

    $.get( "{{url('/')}}/vendor/" + vendor + "/facility", function( data ) {

      $('#facility_idEdit').empty();
      $('#facility_idEdit').append("<option value=''>Select</option>");

      $.each(data, function(index, Object){
        $('#facility_idEdit').append('<option value="'+Object.id+'">'+Object.code + ' - '+Object.name+'</option>');
      });
    });  
  });



  $('#facility_id').on('change', function(e){

    var facility_id = e.target.value;

    $.get( "{{url('/')}}/facility/" + facility_id + "/floor", function( data ) {

      $('#floor_id').empty();
      $('#floor_id').append("<option value=''>Select</option>");

      $.each(data, function(index, Object){
        $('#floor_id').append('<option value="'+Object.id+'">'+Object.name+'</option>');
      });
    });  
  });

  $('#showcontent').on('change', '#facility_idEdit', function(e){

    var facility_id = e.target.value;

    $.get( "{{url('/')}}/facility/" + facility_id + "/floor", function( data ) {

      $('#floor_idEdit').empty();
      $('#floor_idEdit').append("<option value=''>Select</option>");

      $.each(data, function(index, Object){
        $('#floor_idEdit').append('<option value="'+Object.id+'">'+Object.name+'</option>');
      });
    });  
  });

  function getEditForm(id) {

    $("#loadinggif").show();

    $("#preview").find("#showcontent").html("");

    var showUrl = "{{url('/')}}/apartments/"+id+"/edit";
     
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $("#loadinggif").hide();
        $("#preview").find("#showcontent").html(this.responseText);
        $('#datepicker1').datepicker({
          format: 'mm-dd-yyyy',
          autoclose: true
        })
      }
    };
    xhttp.open("GET", showUrl, true);
    xhttp.send();
  }


  $("#showcontent").on('click', '#submitEdit',function(e){

    e.preventDefault();

    var _url = $("#editApartment").attr("action");

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var _data = $("#editApartment").serialize();

      $.ajax({

          url: _url,

          type:'POST',

          dataType:"json",

          data:_data,

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

  function printUpdateError(msg) {
    $("#error_messages").find("ul").html('');
    $("#error_messages").css('display','block');
    $.each( msg, function( key, value ) {
      $("#error_messages").find("ul").append('<li>'+value+'</li>');
    });
  }


  function activationAction(id) {
    $('#preview').modal('show');

    var showUrl = "{{url('/')}}/apartments/"+id+"/activation";
     
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
      //'ordering'    : [[ 2, 'asc' ]],
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })

</script>

@endsection
