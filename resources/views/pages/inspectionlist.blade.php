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
        Inspection
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Inspection Details</a></li>
        <li><a href="#">Inspection</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">  

      <div class="row">
        <div class="box box-default">
          <div class="box-body">
            @ability('Admin', 'Create-Inspection')
            <div class="col-md-2 col-sm-2 col-md-offset-10 col-sm-offset-10">
              <button class="btn btn-block btn-success btn-flat" data-toggle="modal" onclick="show('{{route('inspections.newForm')}}')">NEW</button> <br>
            </div> 
            @endability
            <table class="table table-bordered table-striped" id="example1" >
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Code</th>
                  <th>Date</th>
                  <th>Building</th>
                  <th>Inspection Type</th>
                  <th>Total Inspected Areas</th>
                  <th>Total Deficiencies</th>
                  <th>Total Weight</th>
                  <th>Weighted Avg.</th>
                  <th>Outcome</th>
                  <th>CAP Due Date</th>
		  <th>Inspector</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($inspections as $key => $ins)
                  <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{$ins->code}}</td>
                    <td>{{date('m-d-Y',strtotime($ins->date))}}</td>
                    <td>{{$ins->Facility->code}} - {{$ins->Facility->name}}</td>
                    <td>
                      @if(!empty($ins->Followed))
                        <span class="btn btn-flat bg-orange btn-xs">Followup</span>
                      @else
                        <span class="btn btn-flat bg-olive btn-xs">Initial</span>
                      @endif
                    </td>
                    <td>{{$ins->total_inspected_area}}</td>
                    <td>{{$ins->Inspectiondetail->count()}}</td>
                    <td>
                      @php
                        $total_weight = 0;
                      @endphp
                      @foreach($ins->Inspectiondetail as $insdet)
                        @php
                          $total_weight += $insdet->Deficiencydetail->weightage;
                        @endphp
                      @endforeach
                      {{$total_weight}}
                    </td>
                    <td>
                      @php
                        $avgScore = (($total_weight/$ins->total_inspected_area) + ($total_weight/$ins->Inspectiondetail->count()))/2;
                      @endphp
                      {{number_format($avgScore,2)}}
                    </td>

		    <td>
			@if($avgScore <= 0)
                            <span class="btn bg-blue btn-flat btn-xs">Very Good</span>
                        @elseif($avgScore > 0 && $avgScore <= 3.0)
                            <span class="btn bg-aqua btn-flat btn-xs">Good</span>
                        @elseif($avgScore > 3.0 && $avgScore<= 5.0)
                            <span class="btn bg-green btn-flat btn-xs">Satisfactory</span>
                        @elseif($avgScore > 5.0 && $avgScore <= 10.0)
                            <span class="btn bg-orange btn-flat btn-xs">Unsatisfactory</span>
                        @else
                            <span class="btn bg-red btn-flat btn-xs">UNACCEPTABLE</span>
                        @endif
		    </td>

		    <td>{{date('m-d-Y',strtotime($ins->cap_due_date))}}</td>
		    <td>{{$ins->Createdby->name}}</td>
                    <td>
                      @if($ins->status == 'INCOMPLETE')
                        <span class="btn bg-maroon btn-flat btn-xs">INCOMPLETE</span>
                      @else
                        <span class="btn bg-olive btn-flat btn-xs">COMPLETED</span>
                      @endif
                    </td>
                    <td style="min-width: 100px">
                      @if($ins->status == 'INCOMPLETE')
                        <span>
                          <a class="btn btn-xs btn-primary btn-flat" onclick="show('{{route('inspections.show',$ins->id)}}')" target="_blank">
                            <i class="fa fa-edit"></i> EDIT
                          </a> 
                        </span>
                      @else
                        <span>
                          <a class="btn bg-aqua btn-xs btn-flat" onclick="window.open('{{route('inspections.details',$ins->id)}}', '_blank', 'toolbar=0,location=0,menubar=0');" target="_blank">
                          <i class="fa fa-eye"></i>
                        </a>
                        </span>
                      @endif

                      <button class="btn btn-xs btn-danger btn-flat" data-toggle="modal" id="showPreview" onclick="deletionAction({{$ins->id}})">
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
    <div class="modal-dialog modal-lg" style="width: 80%"  role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="reset()">
            <span aria-hidden="true">&times;</span>
          </button>
          
        </div>
        <div class="modal-body">
          
          <div class="alert alert-danger print-error-msg" style="display:none">
              <ul></ul>
          </div>

          <div id="content">
            
          </div>

        </div>
      </div>
    </div>
  </div>



  <div class="modal fade" id="preview" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 80%" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="show" onclick="reload()">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title text-center" id="modal-title">NEW INSPECTION</h4>
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


  <div class="modal fade" id="secondOrderPrev" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="show">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger print-error-msg" id="error_messages_2" style="display:none">
              <ul></ul>
          </div>
          <div id="showcontent">
            <form name="followupInspection" id="followupInspection" method="POST" action="{{route('inspections.addFollowUpIns')}}">
              {{csrf_field()}}
              <div class="form-group">
                <label>Inspection Code</label>
                <input type="text" class="form-control" name="code" placeholder="Inspection Code">
              </div>
              <button class="btn btn-flat btn-block btn-primary" type="submit" id="followup">ADD</button>
            </form>
          </div>
          
        </div>
      </div>
    </div>
  </div>

@endsection


@section('footer-resources')

<script type="text/javascript">

  var followup_code = null;

      $("#showcontent").on('click','#submit',function(e){

        e.preventDefault();

        $("#modal-title").show();

        $("#facility_id").attr("disabled", false);

        $(".print-error-msg").find("ul").html('');

        var _url = $("#inspections").attr("action");


        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var _data = $("#inspections").serialize() + '&followup_code='+followup_code;

          $.ajax({

              url: _url,

              type:'POST',

              dataType:"json",

              data:_data,

              success: function(data) {
                  if($.isEmptyObject(data.error)){
                    swal({
                      title: "Created!",
                      text: "Inspection details recorded.",
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


      $("#showcontent").on('click','#submitEdit',function(e){

        e.preventDefault();

        $(".print-error-msg").find("ul").html('');

        var _url = $("#inspections").attr("action");

        $("#facility_id").attr("disabled", false);

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var _data = $("#inspections").serialize() + '&followup_code='+followup_code;

          $.ajax({

              url: _url,

              type:'POST',

              dataType:"json",

              data:_data,

              success: function(data) {
                  if($.isEmptyObject(data.error)){
                    swal({
                      title: "Updated!",
                      text: "Inspection details recorded.",
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


      function deletionAction(id) {
        $("#modal-title").hide();
        $('#preview').modal('show');

        var showUrl = "{{url('/')}}/inspections/"+id+"/deletion";
         
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


      $(document).ajaxStart(function () {
          $("#loading").show();
          $("#submit").hide();
          $("#submitEdit").hide();
      }).ajaxStop(function () {
          $("#loading").hide();
          $("#submit").show();
           $("#submitEdit").show();
      });


      function printErrorMsg (msg) {
        $(".print-error-msg").find("ul").html('');
        $(".print-error-msg").css('display','block');
        $.each( msg, function( key, value ) {
          $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
        });

      }


  function getDef(id,elemId) {

    var element = 'details'+elemId;

    $.ajax({
        dataType: "json",
        url: "{{url('/')}}/inspections/defdetails/"+id,
        success: function (data) {
          $('#'+element).find('option').remove();
          $('#'+element).append('<option value="">SELECT</option>');

          $.each(data, function(index, Object){


            $('#'+element).append('<option value="'+Object.id+'">'+Object.description+'</option>');





          });
        }
    });

  }

  $('#preview').on('hidden.bs.modal', function () {
    x = 0;
    window.location.reload();
  });

  function reload() {
    x = 0;
    window.location.reload();
  }


  function getCon(id,elemId) {

    var element1 = 'concern'+elemId;
    var element2 = 'weightage'+elemId;

    $.ajax({
      dataType: "json",
      url: "{{url('/')}}/inspections/getconcern/"+id,
      success: function (data) {
        $('#'+element1).html(data.concern);
        $('#'+element2).val(data.id);
      }
    });
    
  }


  $("#showcontent").on('click','#show',function(){
    $("#preview").find("#showcontent").html("");
    $('#preview').modal('hide');
    $("#loadinggif").show();

  });

  function show(url) {
    
    var showUrl = "{{url('/')}}/inspections/new/form";
    $('#preview').modal({backdrop: 'static', keyboard: false});  

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $("#loadinggif").hide();
        $("#preview").find("#showcontent").html(this.responseText);

      }
    };
    xhttp.open("GET", url, true);
    xhttp.send();
  }


 /* 
  Requested vendor field removal
 $('#vendor_id').on('change', function(e){

    var vendor = e.target.value;

    $.get( "{{url('/')}}/vendor/" + vendor + "/facility", function( data ) {

      $('#facility_id').empty();
      $('#facility_id').append("<option value=''>Select</option>");

      $.each(data, function(index, Object){
        $('#facility_id').append('<option value="'+Object.id+'">'+Object.name+'</option>');
      });
    });  
  });
  */


  $('#showcontent').on('change', '#facility_id', function(e){

    var vendor = e.target.value;

    $.get( "{{url('/')}}/facility/apartment/"+vendor, function( data ) {

      $('#apartment_id').empty();
      $('#apartment_id').append("<option value=''>Select</option>");

      $.each(data, function(index, Object){
        $('#apartment_id').append('<option value="'+Object.id+'">'+Object.name+'</option>');
      });
    });  

  });



  $("#followup").click(function(e){

    e.preventDefault();

    var _url = $("#followupInspection").attr("action");


    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var _data = $("#followupInspection").serialize();

      $.ajax({

          url: _url,

          type:'POST',

          dataType:"json",

          data:_data,

          success: function(data) {

              if($.isEmptyObject(data.error)){

                $('#secondOrderPrev').modal('hide');
                $('#addFollowupButton').hide();
                $('#followup_code').html("Following Inspection: <a href='#'>"+data.success.code+"</a>&nbsp;&nbsp;&nbsp;<button class='btn btn-xs btn-danger' onclick='removeFollowup()'>Remove</button> <span id='followup_id' hidden>"+data.success.id+"</span>");

                followup_code = data.success.id;

              }else{
                
                $("#error_messages_2").find("ul").html('');
                $("#error_messages_2").css('display','block');
                $.each( data.error, function( key, value ) {
                  $("#error_messages_2").find("ul").append('<li>'+value+'</li>');
                });

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

  function isEmpty( el ){
      return !$.trim(el.html())
  }

</script>

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

<link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">

<!-- Page script -->
<script type="text/javascript">

  $(function () {
    //Date picker
    $('#datepicker').datepicker({
      format: 'mm-dd-yyyy',
      autoclose: true
    })

    $('#datepicker1').datepicker({
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
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })


</script>

@endsection