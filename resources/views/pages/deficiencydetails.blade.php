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
        Deficiency Details
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Inspection Details</a></li>
        <li><a href="#">Deficiency Details</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="box box-default">
          <div class="box-body">
            @ability('Admin', 'Create-Deficiency-Detail')
              <div class="col-md-2 col-sm-2 col-md-offset-10 col-sm-offset-10">
                <button class="btn btn-block btn-success btn-flat" data-toggle="modal" data-target="#myModal">NEW</button> <br>
              </div> 
            @endability
            <table class="table table-bordered table-striped" id="example1">
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Code</th>
                  <th>Deficiency Category</th>
                  <th>Deficiency Concern</th>
                  <th>Description</th>
                  <th>Weightage</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($defdetails as $key => $details)
                  <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{$details->code}}</td>
                    <td>{{$details->Category->name}}</td>
                    <td>{{$details->Concern->name}}</td>
                    <td>{{$details->description}}</td>
                    <td>{{$details->weightage}}</td>
                    <td>
                      @if($details->active == '1')
                        <button class="btn bg-olive btn-flat btn-xs">Active</button>
                      @else
                        <button class="btn bg-maroon btn-flat btn-xs">Inactive</button>
                      @endif
                    </td>
                    <td style="min-width: 100px">

                      <button class="btn btn-xs btn-info btn-flat" data-toggle="modal" id="showPreview" onclick="show({{$details->id}})">
                        <span class="fa fa-eye"></span>
                      </button>

                      <button class="btn btn-xs btn-danger btn-flat" data-toggle="modal" id="showPreview" onclick="activationAction({{$details->id}})">
                        <span class="fa fa-power-off"></span>
                      </button>

                      <button class="btn btn-xs btn-danger btn-flat" data-toggle="modal" id="showPreview" onclick="deletionAction({{$details->id}})">
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
          <h4 class="modal-title text-center">NEW DEFICIENCY DETAIL</h4>
        </div>
        <div class="modal-body">
          
          <div class="alert alert-danger print-error-msg" style="display:none">
              <ul></ul>
          </div>

          <form id="deficiencydetails" action="{{route('deficiencydetails.store')}}" method="POST">

            {{csrf_field()}}

            <div class="form-group">
             <label>Deficiency Category <code>*</code></label>
             <select id="category_id" name="category_id" class="form-control" required="">
               <option value="">SELECT</option>
               @foreach($cats as $cat)
                <option value="{{$cat->id}}">{{$cat->name}}</option>
               @endforeach
             </select>
            </div>

            <div class="form-group">
             <label>Deficiency Concern <code>*</code></label>
             <select id="concern_id" name="concern_id" class="form-control" required="">
               <option value="">SELECT</option>
               @foreach($concerns as $con)
                <option value="{{$con->id}}">{{$con->name}}</option>
               @endforeach
             </select>
            </div>

            <div class="form-group">
             <label>Weightage <code>*</code></label>
             <input type="text" name="weightage" placeholder="Weightage" class="form-control" required>
            </div>

            <div class="form-group">
             <label>Description <code>*</code></label>
             <textarea type="text" name="description" placeholder="Description" class="form-control" required></textarea>
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

<script type="text/javascript">
  
  $(document).ready(function() {

      $("#submit").click(function(e){

        e.preventDefault();

        var _url = $("#deficiencydetails").attr("action");


        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var _data = $("#deficiencydetails").serialize();

          $.ajax({

              url: _url,

              type:'POST',

              dataType:"json",

              data:_data,

              success: function(data) {

                  if($.isEmptyObject(data.error)){
                    swal({
                      title: "Created!",
                      text: "Deficiency Details Stored",
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

  function show(id) {
    $('#preview').modal('show');

    var showUrl = "{{url('/')}}/deficiencydetails/"+id;
     
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

    var showUrl = "{{url('/')}}/deficiencydetails/"+id+"/deletion";
     
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

    var showUrl = "{{url('/')}}/deficiencydetails/"+id+"/edit";
     
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


  $("#showcontent").on('click', '#submitEdit',function(e){

    e.preventDefault();

    console.log("Triggered");

    var _url = $("#editDefdet").attr("action");

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var _data = $("#editDefdet").serialize();

      $.ajax({

          url: _url,

          type:'POST',

          dataType:"json",

          data:_data,

          success: function(data) {

              if($.isEmptyObject(data.error)){
                console.log(data);
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
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })

  function activationAction(id) {
    $('#preview').modal('show');

    var showUrl = "{{url('/')}}/deficiencydetails/"+id+"/activation";
     
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

@endsection