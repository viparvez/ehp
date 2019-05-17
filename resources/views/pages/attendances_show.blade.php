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
        Sign In Management
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Show</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="box box-default">
          <div class="box-body">

            <form class="" method='POST' action="{{route('attendances.postByDate')}}">
              {{csrf_field()}}
              <div class="col-md-4 col-sm-4 col-md-offset-4 col-sm-offset-4"> 
                <div class="form-group">
                  <div class="input-group date moveindate">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="text" class="form-control pull-right" id="datepicker" name="date" value="{{$date}}">
                  </div>
                </div>
                
              </div> 

              <div class="col-md-4 col-sm-4">
                  <div class="form-group">
                    <button class="btn btn-success">SEARCH</button>
                  </div>                
              </div> 
            </form>
	

            <table id="example1" class="table table-bordered table-striped table-responsive">
              <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Facility </th>
                <th>Address</th>
                <th>Apartments Occupied</th>
                <th>Signs Received</th>
                <th>#of Signs Today</th>
              </tr>
              </thead>
              <tbody>
                @foreach($data as $key => $value)
                  <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{$value->occ_fac_code}} - {{$value->facility_name}}</td>
                    <td>{{$value->facility_address}}</td>
                    <td>{{$value->total_occupied}}</td>
                    <td>{{$value->total_signs}}</td>
                    <td>{{$value->sign_percentage}} %</td>
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