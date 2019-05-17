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
        Clients
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Client Management</a></li>
        <li><a href="#">Clients</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="box box-default">
          <div class="box-body">
            @ability('Admin', 'Create-Client')
              <div class="col-md-2 col-sm-2 col-md-offset-10 col-sm-offset-10">
                <button class="btn btn-block btn-success btn-flat" data-toggle="modal" data-target="#myModal">NEW</button> <br>
              </div> 
            @endability
            <table id="example1" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Code</th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>SSN</th>
                  <th>Medica ID</th>
                  <th>DOB</th>
                  <th>Comment</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($clients as $key => $client)
                  <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{$client->code}}</td>
                    <td>{{$client->fname}}</td>
                    <td>{{$client->lname}}</td>
                    <td>{{Utilities::my_simple_crypt($client->ssn,'d')}}</td>
                    <td>{{Utilities::my_simple_crypt($client->medicaid,'d')}}</td>
                    <td>
                      @if(!empty($client->dob))
                        {{date('m-d-Y',strtotime($client->dob))}}
                      @else
                      @endif
                    </td>

                    <td>{{$client->comment}}</td>
                    <td>
                      @if($client->Precondition->name == 'Admitted')
                        <button class="btn btn-xs bg-green btn-flat">Admitted</button>
                      @elseif($client->Precondition->name == 'Referral')
                        <button class="btn btn-xs bg-aqua btn-flat">Referral</button>
                      @elseif($client->Precondition->name == 'Discharged')
                        <button class="btn btn-xs btn-danger btn-flat">Discharged</button>
                      @else
                        <button class="btn btn-xs btn-flat">{{$client->Precondition->name}}</button>
                      @endif
                    </td>
                    <td style="min-width: 100px">
                      
                      <button class="btn btn-xs btn-info btn-flat" data-toggle="modal" id="showPreview" onclick="show({{$client->id}})">
			  <span class="fa fa-eye"></span>
                      </button>
                                            
                      <button class="btn btn-xs btn-danger btn-flat" data-toggle="modal" id="" onclick="deletionAction({{$client->id}})">
                          <span class="fa fa-trash"></span>
                      </button>
                                            
                      <button class="btn btn-xs bg-navy btn-flat" data-toggle="modal" id="" onclick="history({{$client->id}})">
                          <span class="fa fa-history"></span>
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
          <h4 class="modal-title text-center">NEW CLIENT CREATION FORM</h4>
        </div>
        <div class="modal-body">
          
          <div class="alert alert-danger print-error-msg" style="display:none">
              <ul></ul>
          </div>

          <form id="clients" action="{{route('clients.store')}}" method="POST" enctype="multipart/form-data">

            {{csrf_field()}}

            <input type="hidden" name="precondition" value="Referral">
              
            <div class="form-group">
             <label>First Name <code>*</code></label>
             <input type="text" name="fname" placeholder="First Name" class="form-control" required>
            </div>

            <div class="form-group">
             <label>Last Name <code>*</code></label>
             <input type="text" name="lname" placeholder="Last Name" class="form-control" required>
            </div>

            <div class="form-group">
             <label>SSN <code>*</code></label>
             <input type="text" name="ssn" placeholder="XXX-XX-XXXX" class="form-control" data-inputmask='"mask": "***-**-****"' data-mask required>
            </div>

            <div class="form-group">
             <label>Medication ID No</label>
             <input type="text" name="medicaid" placeholder="Medication ID" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Date of Birth</label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="text" class="form-control pull-right" id="datepicker" name="dob">
              </div>
            </div>

            <div class="form-group">
             <label>Email</label>
             <input type="email" name="email" placeholder="Email" class="form-control" required>
            </div>

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
              <label>Comment</label>
              <textarea cols="3" name="comment" class="form-control"></textarea>
            </div>

            <div class="col-md-6 col-xs-12">
              <div class="form-group">
                <label>Client's Photo</label>
                <input type="file" name="img_url" id="img_url">
              </div>
            </div>
            
            <div class="col-md-6 col-xs-12">
              <div class="form-group">
                <label>Referral Letter</label>
                <input type="file" name="ref" id="ref" >
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
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="show" onclick="javascript:window.location.reload()">
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


  <div class="modal fade" id="history" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title text-center">HISTORIES</h4>
        </div>
        <div class="modal-body">
         
          <div class="text-center">
            <img src="public/images/loading.gif" id="loadinggif_hist">
          </div>
          
          <div id="showcontent_hist">
            
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

        var _url = $("#clients").attr("action");


        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var _data = $("#clients").serialize();

        var formData = new FormData($('#clients')[0]);

          $.ajax({

              url: _url,

              type:'POST',

              processData: false,

              contentType: false,

              dataType:"json",


              data:formData,

              success: function(data) {

                  if($.isEmptyObject(data.error)){
                    swal({
                      title: "Created!",
                      text: "New Client Added",
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

      $(document).on("submit", "#status", function (e) {
          e.preventDefault();
          var _url = $("#status").attr("action");

          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
          });

          var _data = $("#status").serialize();

          $.ajax({

              url: _url,

              type:'POST',

              dataType:"json",

              data:_data,

              beforeSend: function() { 
                 $("#statupdate").hide();
                 $("#loading2").show();
              },

              success: function(data) {

                  if($.isEmptyObject(data.error)){
                    swal({
                      title: "Created!",
                      text: "Status Updated",
                      icon: "success",
                      button: false,
                      timer: 2000,
                      showCancelButton: false,
                      showConfirmButton: false
                    }).then(
                      function () {
                        loadContent($("input[name='client_id']").val());
                      },
                    );

                  }else{
                    printErrorMsg(data.error);
                  }

              },

          });
      });

  });

  $("#show").click(function(){
    $("#preview").find("#showcontent").html("");
    $('#preview').modal('hide');
    $("#loadinggif").show();
  });

  function show(id) {
    $('#preview').modal('show');

    var showUrl = "{{url('/')}}/clients/"+id;
     
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $("#loadinggif").hide();
        $("#preview").find("#showcontent").html(this.responseText);
        $('#datepicker1').datepicker({
          format: 'mm-dd-yyyy',
          autoclose: true
        })
        $('#datepicker2').datepicker({
          format: 'mm-dd-yyyy',
          autoclose: true
        })

        $('#datepicker3').datepicker({
          format: 'mm-dd-yyyy',
          autoclose: true
        })

      }
    };
    xhttp.open("GET", showUrl, true);
    xhttp.send();
  }


  function loadContent(id) {
    var showUrl = "{{url('/')}}/clients/"+id;
     
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $("#loadinggif").hide();
        $("#preview").find("#showcontent").html(this.responseText);
        $('#datepicker1').datepicker({
          format: 'mm-dd-yyyy',
          autoclose: true
        })
        $('#datepicker2').datepicker({
          format: 'mm-dd-yyyy',
          autoclose: true
        })
      }
    };
    xhttp.open("GET", showUrl, true);
    xhttp.send();
  }

  $('#preview').on('hidden.bs.modal', function () {
    window.location.reload();
  });


  function getEditForm(id) {

    $("#loadinggif").show();

    $("#preview").find("#showcontent").html("");

    var showUrl = "{{url('/')}}/clients/"+id+"/edit";
     
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $("#loadinggif").hide();
        $("#preview").find("#showcontent").html(this.responseText);
        $('#datepicker1').datepicker({
          format: 'mm-dd-yyyy',
          autoclose: true
        })
        $('#datepicker2').datepicker({
          format: 'mm-dd-yyyy',
          autoclose: true
        })

        document.getElementById('ssn').addEventListener('blur', function (e) {
          var x = e.target.value.replace(/\D/g, '').match(/(\d{3})(\d{2})(\d{4})/);
          e.target.value = x[1] + '-' + x[2] + '-' + x[3];
        });

      }
    };
    xhttp.open("GET", showUrl, true);
    xhttp.send();
  }


  $("#showcontent").on('click', '#submitEdit',function(e){

    e.preventDefault();

    var _url = $("#editClient").attr("action");

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var _data = $("#editClient").serialize();

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
                
                printErrorMsg(data.error);

              }

          }

      });

  });


function admission(e){

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
                  title: "Updated!",
                  text: "Admission Successful",
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

  };


  $("#showcontent").on('submit', '#disForm',function(e){

    e.preventDefault();

    var _url = $("#disForm").attr("action");

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var _data = $("#disForm").serialize();

    var formData = new FormData($('#disForm')[0]);

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
                  text: "Discharged",
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

   $("#showcontent").on('click', '#submitTransfer',function(e){

    e.preventDefault();

    var _url = $("#clientTransfer").attr("action");

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var _data = $("#clientTransfer").serialize();

      $.ajax({

          url: _url,

          type:'POST',

          dataType:"json",

          data:_data,

          success: function(data) {

              if($.isEmptyObject(data.error)){

                swal({
                  title: "Updated!",
                  text: "Transferred",
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
                
                printErrorMsgTransfer(data.error);

              }

          }

      });

  });

  function printErrorMsgTransfer(msg) {
    $("#error_messages_transfer").find("ul").html('');
    $("#error_messages_transfer").css('display','block');
    $.each( msg, function( key, value ) {
      $("#error_messages_transfer").find("ul").append('<li>'+value+'</li>');
    });
  }

  function printErrorMsg(msg) {
    $("#error_messages").find("ul").html('');
    $("#error_messages").css('display','block');
    $.each( msg, function( key, value ) {
      $("#error_messages").find("ul").append('<li>'+value+'</li>');
    });
  }


  $('#showcontent').on('change','#building', function(e){

    var building = e.target.value;
    var moveindate = $("#datepicker2").val();

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
*/  });

  function today(){

    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!
    var yyyy = today.getFullYear();

    if(dd<10) {
        dd = '0'+dd
    } 

    if(mm<10) {
        mm = '0'+mm
    } 

    return mm + '-' + dd + '-'+ yyyy;

  }


  function deletionAction(id) {
    $('#preview').modal('show');

    var showUrl = "{{url('/')}}/clients/"+id+"/deletion";
     
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

  $('#showcontent').on('change','#building_2', function(e){

    var building_2 = e.target.value;

    $.get( "{{url('/')}}/facility/" + building_2 + "/apartment/"+today(), function( data ) {

      $('#apartment_id_2').empty();
      $('#apartment_id_2').append("<option value=''>Select</option>");

      $.each(data, function(index, Object){
        $('#apartment_id_2').append('<option value="'+Object.id+'">'+Object.name+'</option>');
      });
    }); 

  });



  function history(id) {
    $('#history').modal('show');

    var showUrl = "{{url('/')}}/client/"+id+"/history";

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $("#loadinggif_hist").hide();
        $("#history").find("#showcontent_hist").html(this.responseText);
      }
    };
    xhttp.open("GET", showUrl, true);
    xhttp.send();
    
  }

  $("#showcontent_hist").on('click', '#load_1 .pagination a', function(e){

    e.preventDefault();
    var pagenumber = $(this).attr('href').split('page=')[1];
    var clientid = $(this).attr('href').split('client/')[1].split('/')[0];

    $.ajax({

      url: 'client/'+clientid+'/history/admissions/?page='+pagenumber

    }).done(function(data){
      $("#load_1").html(data);
    });

  });



  $("#showcontent_hist").on('click', '#load_2 .pagination a', function(e){

    e.preventDefault();
    var pagenumber = $(this).attr('href').split('page=')[1];
    var clientid = $(this).attr('href').split('client/')[1].split('/')[0];

    $.ajax({

      url: 'client/'+clientid+'/history/transfer/?page='+pagenumber

    }).done(function(data){
      $("#load_2").html(data);
    });

  });


  $("#showcontent_hist").on('click', '#load_2 .pagination a', function(e){

    e.preventDefault();
    var pagenumber = $(this).attr('href').split('page=')[1];
    var clientid = $(this).attr('href').split('client/')[1].split('/')[0];

    $.ajax({

      url: 'client/'+clientid+'/history/transfer/?page='+pagenumber

    }).done(function(data){
      $("#load_2").html(data);
    });

  });


  $("#showcontent_hist").on('click', '#load_3 .pagination a', function(e){

    e.preventDefault();
    var pagenumber = $(this).attr('href').split('page=')[1];
    var clientid = $(this).attr('href').split('client/')[1].split('/')[0];

    $.ajax({

      url: 'client/'+clientid+'/history/precondition/?page='+pagenumber

    }).done(function(data){
      $("#load_3").html(data);
    });

  });

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
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })

</script>

@endsection
