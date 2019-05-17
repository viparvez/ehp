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
@endsection

@section('content')
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Vendors
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Vendors</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <div class="box box-default">
          <div class="box-body">
            <div class="col-md-2 col-sm-2 col-md-offset-10 col-sm-offset-10">
              @permission('Create-Vendor')
              <button class="btn btn-block btn-success btn-flat" data-toggle="modal" data-target="#myModal">NEW</button> <br>
              @endpermission
            </div> 
            <table id="example1" class="table table-bordered table-striped table-responsive">
              <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Name </th>
                <th>Address</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Fax</th>
                <th># of Facilities</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
              </thead>
              <tbody>
                @foreach($vendors as $key => $vendor)
                  <tr>
                    <td>{{ $key+1 }}</td>
                    <td><a href="{{route('vendorFacilities',$vendor->id)}}"> {{$vendor->name}}</a></td>
                    <td>{{$vendor->address}} {{$vendor->city}}, {{$vendor->State->name}}, {{$vendor->zip}}</td>
                    <td>{{$vendor->email}}</td>
                    <td>{{$vendor->phone}}</td>
                    <td width="100">{{$vendor->fax}}</td>
                    <td> 
                      <button class="btn bg-orange btn-flat btn-xs">{{count($vendor->Facility->where('active','1'))}}</button>
                    </td>
                    <td>
                      @if($vendor->active == '1')
                        <button class="btn bg-olive btn-flat btn-xs">Active</button>
                      @else
                        <button class="btn bg-maroon btn-flat btn-xs">Inctive</button>
                      @endif
                    </td>
                    <td style="min-width: 100px">
                      @ability('Admin','View-Vendor-Details')
                      <button class="btn btn-xs btn-info btn-flat" data-toggle="modal" id="showPreview" onclick="showVendor({{$vendor->id}})">
                        <span class="fa fa-eye"></span>
                      </button>
                      @endability

                      @ability('Admin','Vendor-Activation')
                      <button class="btn btn-xs btn-warning btn-flat" data-toggle="modal" id="showPreview" onclick="activationAction({{$vendor->id}})">
                        <span class="fa fa-power-off"></span>
                      </button>
                      @endability
                      
                      @ability('Admin','Delete-Vendor')
                      <button class="btn btn-xs btn-danger btn-flat" data-toggle="modal" id="showPreview" onclick="deletionAction({{$vendor->id}})">
                        <span class="fa fa-trash"></span>
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
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title text-center">NEW VENDOR CREATION FORM</h4>
        </div>
        <div class="modal-body">
          
          <div class="alert alert-danger print-error-msg" style="display:none">
              <ul></ul>
          </div>

          <form id="vendor" action="{{route('vendors.store')}}" method="POST">

            {{csrf_field()}}

            <div class="form-group">
             <label>Name <code>*</code></label>
             <input type="text" name="name" placeholder="Name" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Address <code>*</code></label>
              <textarea rows="2" class="form-control" name="address"></textarea>
            </div>

            <div class="form-group">
              <label>City <code>*</code></label>
              <input type="text" name="city" class="form-control" placeholder="City" required>
            </div>

            <div class="form-group">
              <label>State <code>*</code></label>
              <select name="state_id" class="form-control">
                <option value="" selected="">Select</option>
              @foreach($states as $state)
                <option value="{{$state->id}}">{{$state->name}}</option>
              @endforeach
              </select>
            </div>

            <div class="form-group">
              <label>ZIP <code>*</code></label>
              <input type="text" name="zip" class="form-control" placeholder="ZIP" required>
            </div>

            <div class="form-group">
              <label>Contact Person</label>
              <input type="text" name="contact_person" class="form-control" placeholder="Contact Person" required>
            </div>

            <div class="form-group">
              <label>Email <code>*</code></label>
              <input type="text" name="email" class="form-control" placeholder="Email" required>
            </div>

            <div class="form-group">
              <label>Phone <code>*</code></label>
              <input type="text" name="phone" class="form-control" placeholder="Phone" data-inputmask='"mask": "(999) 999-9999"' data-mask="" required>
            </div>

            <div class="form-group">
              <label>FAX</label>

              <div class="input-group">
                <div class="input-group-addon">
                  <i class="fa fa-fax"></i>
                </div>
                  <input type="text" name="fax" id="fax" class="form-control" placeholder="FAX" required>
              </div>
            </div>

            <button class="btn btn-block btn-primary btn-sm" id="createVendor" type="submit">SUBMIT</button>
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

      $("#createVendor").click(function(e){

        e.preventDefault();

        var _url = $("#vendor").attr("action");

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var _data = $("#vendor").serialize();


          $.ajax({

              url: _url,

              type:'POST',

              dataType:"json",

              data:_data,

              success: function(data) {

                  if($.isEmptyObject(data.error)){

                    swal({
                      title: "Created!",
                      text: "New vendor created",
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
          $("#createVendor").hide();
      }).ajaxStop(function () {
          $("#loading").hide();
          $("#createVendor").show();
      });


      function printErrorMsg (msg) {
        $(".print-error-msg").find("ul").html('');
        $(".print-error-msg").css('display','block');
        $.each( msg, function( key, value ) {
          $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
        });

      }

  });

  $("#closeVdrShow").click(function(){
    $("#preview").find("#showcontent").html("");
    $('#preview').modal('hide');
    $("#loadinggif").show();
  });

  function showVendor(id) {
    $('#preview').modal('show');

    var showUrl = "{{url('/')}}/vendors/"+id;
     
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


  function activationAction(id) {
    $('#preview').modal('show');

    var showUrl = "{{url('/')}}/vendors/"+id+"/activation";
     
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

    var showUrl = "{{url('/')}}/vendors/"+id+"/deletion";
     
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

    var showUrl = "{{url('/')}}/vendors/"+id+"/edit";
     
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $("#loadinggif").hide();
        $("#preview").find("#showcontent").html(this.responseText);
        $('#datepicker1').datepicker({
          format: 'yyyy-mm-dd',
          autoclose: true
        })
      }
    };
    xhttp.open("GET", showUrl, true);
    xhttp.send();
  }


  $("#showcontent").on('click', '#editData', function(e){

    e.preventDefault();

    var _updateURL = $("#editVendor").attr("action");

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var _data = $("#editVendor").serialize();


      $.ajax({

          url: _updateURL,

          type:'POST',

          dataType:"json",

          data:_data,

          success: function(data) {

              if($.isEmptyObject(data.error)){

                swal({
                  title: "Updated!",
                  text: "Data Updated!",
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
      //'ordering'    : [[ 1, 'asc' ]],
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })

  function dpick(){
    $('#datepicker1').datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true
    })
  }

</script>

@endsection