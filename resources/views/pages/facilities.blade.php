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
        Facilities
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Location Management</a></li>
        <li><a href="#">Facilities</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
    
      <div class="row">
        <div class="box box-default">
          <div class="box-body">
            @ability('Admin', 'Create-Facility')
            <div class="col-md-2 col-sm-2 col-md-offset-10 col-sm-offset-10">
              <button class="btn btn-block btn-success btn-flat" data-toggle="modal" data-target="#myModal">NEW</button> <br>
            </div> 
            @endability
            <table id="example1" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Code</th>
                  <th>Name/Address</th>
                  <th>Address</th>
                  <th>Vendor</th>
                  <th>Contact Info</th>
                  <th>Rate</th>
                  <th>#Of Units</th>
                  <th>#Of Occupied</th>
                  <th>#Of Vacant</th>
                  <th>Type</th>
                  <th>Comments</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
              @foreach($facilities as $key => $facility)
                <tr>
                  <td>{{ $key + 1 }}</td>
                  <td>{{$facility->code}}</td>
                  <td><a href="{{route('facilityApt', $facility->id)}}">{{$facility->name}}</a></td>
                  <td>
                      @if(!empty($facility->city))
                        {{$facility->city}}, 
                      @else
                      @endif
                      
                      @if(!empty($facility->State->name))
                        {{$facility->State->name}},
                      @else
                      @endif
                      
                      @if(!empty($facility->zip))
                       {{$facility->zip}}
                      @else
                      @endif
                  </td>
                  <td>{{$facility->Vendor->name}}</td>
                  <td>
                    @if(!empty($facility->contact_p))
                      {{$facility->contact_p}}<br>
                      {{$facility->phone}}
                    @else
                      {{$facility->phone}}
                    @endif
                  </td>
                  <td>
                    @if(!empty($facility->rate))
                      ${{$facility->rate}}
                    @else
                    @endif
                  </td>
                  <td>{{count($facility->Apartment($facility->id))}}</td>
                  <td>
                    <button class="btn bg-orange btn-flat btn-xs">{{count($facility->Apartment($facility->id))-$facility->ApartmentVacant($facility->id)}}
                    </button>
                  </td>
                  <td><button class="btn bg-olive btn-flat btn-xs">{{$facility->ApartmentVacant($facility->id)}}</button></td>
                  <td>{{$facility->type}}</td>
                  <td>{{$facility->comment}}</td>
                  <td>
                    @if($facility->active == '1')
                      <button class="btn bg-olive btn-flat btn-xs">Active</button>
                    @else
                      <button class="btn bg-maroon btn-flat btn-xs">Offline</button>
                    @endif
                  </td>
                  <td style="min-width: 100px">

                    <button class="btn btn-xs btn-info btn-flat" data-toggle="modal" id="showPreview" onclick="show({{$facility->id}})">
                      <span class="fa fa-eye"></span>
                    </button>

                    <button class="btn btn-xs btn-danger btn-flat" data-toggle="modal" id="showPreview" onclick="activationAction({{$facility->id}})">
                      <span class="fa fa-power-off"></span>
                    </button>

                    <button class="btn btn-xs btn-danger btn-flat" data-toggle="modal" id="showPreview" onclick="deletionAction({{$facility->id}})">
                      <span class="fa fa-trash"></span>
                    </button>
                  </td>
                </tr>
              @endforeach
              <tbody>
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
          <h4 class="modal-title text-center">NEW FACILITY CREATION FORM</h4>
        </div>
        <div class="modal-body">
          
          <div class="alert alert-danger print-error-msg" style="display:none">
              <ul></ul>
          </div>

          <form id="facilities" action="{{route('facilities.store')}}" method="POST" enctype="multipart/form-data">

            {{csrf_field()}}

          <div class="col-md-6 col-sm-12 col-xs-12">

            <div class="form-group">
              <label>Vendor <code>*</code></label>
              <select name="vendor_id" class="form-control">
                <option value="" selected="">Select</option>
              @foreach($vendors as $vendor)
                <option value="{{$vendor->id}}">{{$vendor->name}}</option>
              @endforeach
              </select>
            </div>

            <div class="form-group">
             <label>Name/Address <code>*</code></label>
             <input type="text" name="name" placeholder="Name" class="form-control" required>
            </div>

            <div class="form-group">
             <label>Code <code>*</code></label>
             <input type="text" name="code" id="code" placeholder="Code" class="form-control" required>
            </div>

            <div class="col-md-6 col-sm-12 col-xs-12">

              <div class="form-group">
                <label>Has Medicine? <code>*</code></label><br>
                <input type="radio" name="hasMedicine" id="hasMedicine" value="1" checked> Yes &nbsp;&nbsp;
                <input type="radio" name="hasMedicine" id="hasMedicine" value="0"> No 
              </div>

              <div class="form-group">
                <label>Has Handicap Access? <code>*</code></label><br>
                <input type="radio" name="hasHandicapAccess" id="hasHandicapAccess" value="1" checked> Yes &nbsp;&nbsp;
                <input type="radio" name="hasHandicapAccess" id="hasHandicapAccess" value="0"> No 
              </div>

            </div>

            <div class="col-md-6 col-sm-12 col-xs-12">

              <div class="form-group">
                <label>Is Smoke Free? <code>*</code></label><br>
                <input type="radio" name="isSmokeFree" id="isSmokeFree" value="1" checked> Yes &nbsp;&nbsp;
                <input type="radio" name="isSmokeFree" id="isSmokeFree" value="0"> No 
              </div>

              <div class="form-group">
                <label>Has Elevator? <code>*</code></label><br>
                <input type="radio" name="hasElevator" id="hasElevator" value="1" checked> Yes &nbsp;&nbsp;
                <input type="radio" name="hasElevator" id="hasElevator" value="0"> No 
              </div>

            </div>

            <div class="form-group">
              <label>Facility Type <code>*</code></label><br>
              <select name="type" class="form-control">
                <option value="">SELECT</option>
                <option value="FAMILY">FAMILY</option>
                <option value="SINGLE">SINGLE</option>
              </select>
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
              <input type="text" name="contact_p" class="form-control" placeholder="Contact Person" required>
            </div>

            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>

          </div>

          <div class="col-md-6 col-sm-12 col-xs-12">

            <div class="form-group">
              <label>Phone</label>

              <div class="input-group">
                <div class="input-group-addon">
                  <i class="fa fa-phone"></i>
                </div>
                <input type="text" name="phone" id="phone" class="form-control" data-inputmask='"mask": "(999) 999-9999"' data-mask required>
              </div>
            </div>

            <div class="form-group">
              <label>Start Date (HASA) <code>*</code></label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="text" class="form-control pull-right" id="start_date_hasa" name="start_date_hasa">
              </div>
            </div>

            <div class="form-group">
              <label>Start Date (EHP) <code>*</code></label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="text" class="form-control pull-right" id="start_date_ehp" name="start_date_ehp">
              </div>
            </div>


            <div class="form-group">
              <label>Contract Signed From</label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="text" class="form-control pull-right" id="mou_signed_from" name="mou_signed_from">
              </div>
            </div>


            <div class="form-group">
              <label>Contract Signed To</label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="text" class="form-control pull-right" id="mou_signed_to" name="mou_signed_to">
              </div>
            </div>


            <div class="form-group">
              <label>EIN <code>*</code></label>
              <input type="text" name="ein" class="form-control" placeholder="EIN" required>
            </div>


            <div class="form-group">
              <label>Rate <code>*</code></label>
              <input type="text" name="rate" class="form-control" placeholder="Rate" required>
            </div>


            <div class="form-group">
              <label>No. of Units as per contract</label>
              <input type="text" name="no_of_units" class="form-control" placeholder="No of Units" required>
            </div>


            <div class="form-group">
              <label>Comment</label>
              <textarea rows="2" class="form-control" name="comment" id="comment"></textarea>
            </div>


            <div class="form-group">
              <label>Contract Papers</label>
              <input type="file" name="contact_paper" id="contact_paper">
            </div>
          
          </div>

            <button class="btn btn-block btn-primary btn-sm" id="submit" type="submit">SUBMIT</button>
            <button class="btn btn-block btn-primary btn-sm" id="loading" style="display: none" disabled="">Working...</button>

          </form>

        </div>
      </div>
    </div>
  </div>



  <div class="modal fade" id="preview" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" id="2nOrder" role="document">
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

  $(function () {
    //Date picker
    $('#start_date_hasa').datepicker({
      format: 'mm-dd-yyyy',
      autoclose: true
    })

    $('#start_date_ehp').datepicker({
      format: 'mm-dd-yyyy',
      autoclose: true
    })

    $('#mou_signed_to').datepicker({
      format: 'mm-dd-yyyy',
      autoclose: true
    })

    $('#mou_signed_from').datepicker({
      format: 'mm-dd-yyyy',
      autoclose: true
    })

    //Timepicker
    $('.timepicker').timepicker({
      showInputs: false
    })

    $('[data-mask]').inputmask();
    
  })
  
  $(document).ready(function() {

      $("#submit").click(function(e){

        e.preventDefault();

        var _url = $("#facilities").attr("action");

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var _data = $("#facilities").serialize();

        var formData = new FormData($('#facilities')[0]);


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
                      text: "New Facility Added",
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
    $('#2nOrder').attr("class","modal-dialog modal-lg")

    var showUrl = "{{url('/')}}/facilities/"+id;
     
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

    var showUrl = "{{url('/')}}/facilities/"+id+"/edit";
     
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {

        $("#loadinggif").hide();
        $("#preview").find("#showcontent").html(this.responseText);

        $('.date').datepicker({
          format: 'mm-dd-yyyy',
          autoclose: true
        })
      }
    };
    xhttp.open("GET", showUrl, true);
    xhttp.send();
  }


  $("#showcontent").on('click', '#submitEdit', function(e){

    e.preventDefault();

    var _updateURL = $("#editFacility").attr("action");

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var _data = $("#editFacility").serialize();

    var formData = new FormData($('#editFacility')[0]);


      $.ajax({

          url: _updateURL,

          type:'POST',

          dataType:"json",

          processData: false,

          contentType: false,

          data:formData,

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

  function activationAction(id) {
    $('#preview').modal('show');
    $('#2nOrder').attr("class","modal-dialog")

    var showUrl = "{{url('/')}}/facilities/"+id+"/activation";
     
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

    var showUrl = "{{url('/')}}/facilities/"+id+"/deletion";
     
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


  $(function () {

    $('#start_date_hasa').datepicker({
      format: 'mm-dd-yyyy',
      autoclose: true
    })

    $('#start_date_ehp').datepicker({
      format: 'mm-dd-yyyy',
      autoclose: true
    })

    $('#mou_signed_to').datepicker({
      format: 'mm-dd-yyyy',
      autoclose: true
    })

    $('#mou_signed_from').datepicker({
      format: 'mm-dd-yyyy',
      autoclose: true
    })

    //Timepicker
    $('.timepicker').timepicker({
      showInputs: false
    })

    $('[data-mask]').inputmask();

    //Date picker
    $('#datepicker').datepicker({
      format: 'mm-dd-yyyy',
      autoclose: true
    })

    $('.date').datepicker({
      format: 'mm-dd-yyyy',
      autoclose: true
    })
    
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