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

  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/modules/export-data.js"></script>


@endsection

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
	<small>Version 2.5.1</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
      </ol>
    </section>

    <section class="content">
      <!-- Info boxes -->
      <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="ion ion-ios-contact-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">VENDORS</span>
			  <span class="info-box-number">{{$dataset_1[0]->vendors}}</span>
			  <div class="progress">
                <div class="progress-bar bg-aqua" style="width: 100%"></div>
              </div>
              <span class="info-box-number-left">ACTIVE&nbsp;&nbsp;<small style="color: #00A65A; font-size: 16px; font-weight: bold;">{{$dataset_1[0]->vendors_active}}</small></span>
              <div class="box-tools pull-right">
                    <span class="label label-danger">INACTIVE&nbsp;&nbsp;&nbsp;{{$dataset_1[0]->vendors_inactive}}</span>
              </div>

            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-olive"><i class="ion ion-ios-home-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">FACILITIES</span>
			  <span class="info-box-number">{{$dataset_1[0]->facilities}}</span>
			  <div class="progress">
                <div class="progress-bar bg-olive" style="width: 100%"></div>
              </div>
              <span class="info-box-number-left">ACTIVE&nbsp;&nbsp;<small style="color: #00A65A; font-size: 16px; font-weight: bold;">{{$dataset_1[0]->facilities_active}}</small></span>
              <div class="box-tools pull-right">
		    <span data-toggle="tooltip" title="SINGLE | FAMILY" class="badge bg-green">{{$dataset_1[0]->facilities_single_online}} | {{$dataset_1[0]->facilities_family_online}}</span>
                    <span class="label label-danger">OFFLINE&nbsp;&nbsp;&nbsp;{{$dataset_1[0]->facilities_non_referral}}</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
		</div>

        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-orange"><i class="ion ion-ios-albums-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">UNITS</span>
			  <span class="info-box-number">{{$dataset_1[0]->apartments}}</span>
			  <div class="progress">
                <div class="progress-bar bg-orange" style="width: 100%"></div>
              </div>
              <span class="info-box-number-left">ONLINE&nbsp;&nbsp;<small style="color: #00A65A; font-size: 16px; font-weight: bold;">{{$dataset_1[0]->apartments_online}}</small></span>
			  <div class="box-tools pull-right">
                    <span class="label label-danger">OFFLINE&nbsp;&nbsp;&nbsp;{{$dataset_1[0]->apartments_offline}}</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
		</div>

        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-maroon"><i class="ion ion-ios-people-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">CLIENTS</span>
			  <span class="info-box-number">{{$dataset_1[0]->clients}}</span>
			  <div class="progress">
                <div class="progress-bar bg-maroon" style="width: 100%"></div>
              </div>
              <span class="info-box-number-left">REFERRAL&nbsp;&nbsp;<small style="color: #00A65A; font-size: 16px; font-weight: bold;">{{$dataset_1[0]->clients_referral}}</small></span>
              <div class="box-tools pull-right">
		    <span data-toggle="tooltip" title="ADMITTED | DISCHARGED" class="badge bg-green">{{$dataset_1[0]->clients_admitted}} | {{$dataset_1[0]->clients_discharged}}</span>
                    <span class="label label-danger">NO SHOW&nbsp;&nbsp;&nbsp;{{$dataset_1[0]->clients_noshow}}</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
		</div>

        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-purple"><i class="ion ion-ios-calculator-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">SINGLE UNITS (Active)</span>
			  <span class="info-box-number">{{$dataset_1[0]->apts_single}}</span>
			  <div class="progress">
                <div class="progress-bar bg-purple" style="width: 100%"></div>
              </div>
              <span class="info-box-number-left">OCCUPIED&nbsp;&nbsp;<small style="color: #00A65A; font-size: 16px; font-weight: bold;">{{$dataset_1[0]->apts_single_occupied}}</small></span>
              <div class="box-tools pull-right">
                    <span class="label label-warning">VACANT&nbsp;&nbsp;&nbsp;{{$dataset_1[0]->apts_single_vacant}}</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
		</div>
		
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-blue"><i class="ion ion-ios-calculator-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">FAMILY UNITS (Active)</span>
			  <span class="info-box-number">{{$dataset_1[0]->apts_family}}</span>
			  <div class="progress">
                <div class="progress-bar bg-blue" style="width: 100%"></div>
              </div>
              <span class="info-box-number-left">OCCUPIED&nbsp;&nbsp;<small style="color: #00A65A; font-size: 16px; font-weight: bold;">{{$dataset_1[0]->apts_family_occupied}}</small></span>
              <div class="box-tools pull-right">
                    <span class="label label-warning">VACANT&nbsp;&nbsp;&nbsp;{{$dataset_1[0]->apts_family_vacant}}</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
	</div>

        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-yellow" style="width: 100%; font-size: 24px;">OCCUPIED ({{$dataset_1[0]->occupied}})
	    <div class="box-tools pull-right">
                  @if($dataset_1[0]->apartments_online != 0)
                    <span class="label label-info">{{number_format((($dataset_1[0]->occupied/$dataset_1[0]->apartments_online))*100,2)}}%</span>
                  @else
                  @endif
            </div>
	    </span>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>


        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-green" style="width: 100%; font-size: 24px;">VACANT ({{$dataset_1[0]->vacant}})
	    <div class="box-tools pull-right">
                  @if($dataset_1[0]->apartments_online != 0)
                    <span class="label label-info">{{number_format((($dataset_1[0]->vacant/$dataset_1[0]->apartments_online))*100,2)}}%</span>
                  @else
                  @endif
            </div>
	    </span>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>


      </div>
    </section>
    
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
	      <i class="fa fa-bar-chart"></i>
              <h3 class="box-title">Number of Facilities and Units As Per Contract (City Wide)</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                <div class="col-md-8">

                  <div class="box-body">
                            <table id="example2" class="table table-bordered table-hover">
                <thead>
                <tr>
                  <th>City</th>
                  <th>State</th>
                  <th style="width: 250px">#Of Facilities</th>
                  <th style="width: 250px">#Of Units as per contract</th>
                </tr>
                </thead>
                <tbody>
                
		@foreach($dataset_3 as $cu)
                  <tr>
                    <td><i class="fa fa-info-circle"></i>&nbsp; &nbsp; &nbsp;{{$cu->cities}}</td>
                    <td>{{$cu->states}}</td>
                    <td>
			{{$cu->total_c_facilities}}
			<div class="box-tools pull-right">
					
				@if($cu->cities == 'Bronx')
                      			<span data-toggle="tooltip" title="SINGLE" class="badge bg-green">{{$dataset_1[0]->bronx_fac_single}}</span>
					 <span data-toggle="tooltip" title="FAMILY" class="badge bg-aqua">{{$dataset_1[0]->bronx_fac_family}}</span>
				@else
                   		@endif
				
				@if($cu->cities == 'Brooklyn')
                     			 <span data-toggle="tooltip" title="SINGLE" class="badge bg-green">{{$dataset_1[0]->brooklyn_fac_single}}</span>
					 <span data-toggle="tooltip" title="FAMILY" class="badge bg-aqua">{{$dataset_1[0]->brooklyn_fac_family}}</span>
				@else
                    		@endif
				
				@if($cu->cities == 'New York')
                     			 <span data-toggle="tooltip" title="SINGLE" class="badge bg-green">{{$dataset_1[0]->ny_fac_single}}</span>
					 <span data-toggle="tooltip" title="FAMILY" class="badge bg-aqua">{{$dataset_1[0]->ny_fac_family}}</span>
				@else
                    		@endif
				
				@if($cu->cities == 'Queens')
                     			 <span data-toggle="tooltip" title="SINGLE" class="badge bg-green">{{$dataset_1[0]->queens_fac_single}}</span>
					 <span data-toggle="tooltip" title="FAMILY" class="badge bg-aqua">{{$dataset_1[0]->queens_fac_family}}</span>
                    		@else
                    		@endif
						
			</div>
		      </td>
                    <td>
					{{$cu->total_c_f_units}}
					<div class="box-tools pull-right">
					@if($cu->cities == 'Bronx')
                      <span data-toggle="tooltip" title="SINGLE" class="badge bg-orange">{{$dataset_1[0]->bronx_apts_single}}</span>
					  <span data-toggle="tooltip" title="FAMILY" class="badge bg-red">{{$dataset_1[0]->bronx_apts_family}}</span>
					@else
                    @endif
				
					@if($cu->cities == 'Brooklyn')
                      <span data-toggle="tooltip" title="SINGLE" class="badge bg-orange">{{$dataset_1[0]->brooklyn_apts_single}}</span>
					  <span data-toggle="tooltip" title="FAMILY" class="badge bg-red">{{$dataset_1[0]->brooklyn_apts_family}}</span>
					@else
                    @endif
				
					@if($cu->cities == 'New York')
                      <span data-toggle="tooltip" title="SINGLE" class="badge bg-orange">{{$dataset_1[0]->ny_apts_single}}</span>
					  <span data-toggle="tooltip" title="FAMILY" class="badge bg-red">{{$dataset_1[0]->ny_apts_family}}</span>
					@else
                    @endif
				
					@if($cu->cities == 'Queens')
                      <span data-toggle="tooltip" title="SINGLE" class="badge bg-orange">{{$dataset_1[0]->queens_apts_single}}</span>
					  <span data-toggle="tooltip" title="FAMILY" class="badge bg-red">{{$dataset_1[0]->queens_apts_family}}</span>
                    @else
                    @endif
						
					</div>
					</td>
                  </tr>
		@endforeach
                                  
              </tbody>
            </table>          </div>
                </div>
                <!-- /.col -->


              @if($dataset_1[0]->total_apts_contract != 0)

                <div class="col-md-4">
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">Bronx ({{number_format((($dataset_1[0]->bronx_apts/$dataset_1[0]->total_apts_contract))*100,2)}}%)</span>
                    <span class="progress-number"><b>{{$dataset_1[0]->bronx_apts}}</b>/{{$dataset_1[0]->total_apts_contract}}</span>

                    <div class="progress sm">
                      <div class="progress-bar progress-bar-red" style="width: {{number_format((($dataset_1[0]->bronx_apts/$dataset_1[0]->total_apts_contract))*100)}}%"></div>
                    </div>
                  </div>
		  <div class="progress-group">
                    <span class="progress-text">Brooklyn ({{number_format((($dataset_1[0]->brooklyn_apts/$dataset_1[0]->total_apts_contract))*100,2)}}%)</span>
                    <span class="progress-number"><b>{{$dataset_1[0]->brooklyn_apts}}</b>/{{$dataset_1[0]->total_apts_contract}}</span>

                    <div class="progress sm">
                      <div class="progress-bar progress-bar-aqua" style="width: {{number_format((($dataset_1[0]->brooklyn_apts/$dataset_1[0]->total_apts_contract))*100)}}%"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">New York ({{number_format((($dataset_1[0]->ny_apts/$dataset_1[0]->total_apts_contract))*100,2)}}%)</span>
                    <span class="progress-number"><b>{{$dataset_1[0]->ny_apts}}</b>/{{$dataset_1[0]->total_apts_contract}}</span>

                    <div class="progress sm">
                      <div class="progress-bar progress-bar-green" style="width: {{number_format((($dataset_1[0]->ny_apts/$dataset_1[0]->total_apts_contract))*100)}}%"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">Queens ({{number_format((($dataset_1[0]->queens_apts/$dataset_1[0]->total_apts_contract))*100,2)}}%)</span>
                    <span class="progress-number"><b>{{$dataset_1[0]->queens_apts}}</b>/{{$dataset_1[0]->total_apts_contract}}</span>

                    <div class="progress sm">
                      <div class="progress-bar progress-bar-yellow" style="width: {{number_format((($dataset_1[0]->queens_apts/$dataset_1[0]->total_apts_contract))*100)}}%"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                </div>

                @else
                @endif
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
             <!-- /.box-footer -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
    </section>

    <!-- Main content -->

      <section class="content">
        <div class="row">
		<div class="col-md-6 col-lg-6 col-xs-12 col-sm-6">
		<div class="box box-success">
            	<div class="box-header with-border">
			<i class="fa fa-bar-chart"></i>
			<h3 class="box-title">Vacancy and Occupancy in Last 6 Months</h3>
            	</div>
			<div class="box-body">
              	<div class="row">
                
			<div id="container" style="min-width: 310px; height: 400px; padding: 10px; margin: 0 auto"></div>
				
		</div>
		</div>
		</div>
		  </div>

		  <div class="col-md-6 col-lg-6 col-xs-12 col-sm-6">
		  <div class="box box-warning">
            	  <div class="box-header with-border">
			<i class="fa fa-pie-chart"></i>
			<h3 class="box-title">Percentage of Vacancy in Last 6 Months</h3>
            	</div>
			<div class="box-body">
              	<div class="row">
                
			<div id="piechart" style="min-width: 310px; height: 400px; padding: 10px; margin: 0 auto"></div>
				
		</div>
	      </div>
	    </div>
	  </div>
        </div>
      </section>


    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-danger">
            <div class="box-header with-border">
	      <i class="fa fa-sitemap"></i>
              <h3 class="box-title">Inspection CAP for next 30 days</h3>
            </div>
				
			<div class="box-body">
              <table id="example2" class="table table-bordered table-hover">
                <thead>
                <tr>
                  <th>Inspection Code</th>
                  <th>Building Code</th>
                  <th>Building Name</th>
                  <th>Building Address</th>
                  <th>CAP Due Date</th>
                  <th>Inspector Name</th>
                </tr>
                </thead>
                <tbody>
                
                  @foreach($caps as $cap)
                  <tr>
                    <td>{{$cap->inspection_code}}</td>
                    <td>{{$cap->facility_code}}</td>
                    <td>{{$cap->facility_name}}</td>
                    <td>{{$cap->facility_city}}, {{$cap->state_name}}, {{$cap->facility_zip}}</td>
                    <td>{{date('m-d-Y',strtotime($cap->cap_due_date))}}</td>
                    <td>{{$cap->inspector}}</td>
                  </tr>
                  @endforeach
                
              </tbody>
            </table>
          </div>
		  </div>
        </div>
	  </div>
    </section>
    <!-- /.content -->
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
  
Highcharts.chart('container', {
  chart: {
    type: 'column'
  },
  credits: {
      enabled: false
  },
  exporting: { enabled: false },
  title: {
    text: 'Vacancy and Occupancy'
  },
  xAxis: {
    categories: [
      '{{$barChart[0]->Previous_month}}',
      '{{$barChart[0]->Previous_month_1}}',
      '{{$barChart[0]->Previous_month_2}}',
      '{{$barChart[0]->Previous_month_3}}',
      '{{$barChart[0]->Previous_month_4}}',
      '{{$barChart[0]->Previous_month_5}}',
    ],
    crosshair: true
  },
  yAxis: {
    min: 0,
    title: {
      text: 'Vacancy/Occupancy'
    }
  },
  tooltip: {
    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
      '<td style="padding:0"><b>{point.y:.0f}</b></td></tr>',
    footerFormat: '</table>',
    shared: true,
    useHTML: true
  },
  plotOptions: {
    column: {
      pointPadding: 0.2,
      borderWidth: 0
    }
  },
  series: [{
    name: 'Occupancy',
    data: [
            {{$barChart[1]->Previous_month-$barChart[2]->Previous_month}},
            {{$barChart[1]->Previous_month_1-$barChart[2]->Previous_month_1}},
            {{$barChart[1]->Previous_month_2-$barChart[2]->Previous_month_2}},
            {{$barChart[1]->Previous_month_3-$barChart[2]->Previous_month_3}},
            {{$barChart[1]->Previous_month_4-$barChart[2]->Previous_month_4}},
            {{$barChart[1]->Previous_month_5-$barChart[2]->Previous_month_5}},
          ]

  }, {
    name: 'Vacancy',
    data: [
            {{$barChart[2]->Previous_month}},
            {{$barChart[2]->Previous_month_1}},
            {{$barChart[2]->Previous_month_2}},
            {{$barChart[2]->Previous_month_3}},
            {{$barChart[2]->Previous_month_4}},
            {{$barChart[2]->Previous_month_5}},
          ]

  }]
});


</script>

@if($barChart[1]->Previous_month != 0 || $barChart[1]->Previous_month_1 != 0 || $barChart[1]->Previous_month_2 != 0 || $barChart[1]->Previous_month_3 != 0 || $barChart[1]->Previous_month_4 != 0 || $barChart[1]->Previous_month_5 != 0)

<script type="text/javascript">

Highcharts.chart('piechart', {
  chart: {
    plotBackgroundColor: null,
    plotBorderWidth: null,
    plotShadow: false,
    type: 'pie'
  },
  credits: {
      enabled: false
  },
  exporting: { enabled: false },
  title: {
    text: 'Percentage of Vacancy'
  },
  tooltip: {
    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
  },
  plotOptions: {
    pie: {
      allowPointSelect: true,
      cursor: 'pointer',
      dataLabels: {
        enabled: true,
        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
        style: {
          color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
        }
      }
    }
  },
  series: [{
    name: 'Vacancy',
    colorByPoint: true,
    data: [{
      name: '{{$barChart[0]->Previous_month}}',
      y: {{(($barChart[1]->Previous_month-$barChart[2]->Previous_month)/$barChart[1]->Previous_month)*100}},
      sliced: true,
      selected: true
    }, {
      name: '{{$barChart[0]->Previous_month_1}}',
      y: {{(($barChart[1]->Previous_month_1-$barChart[2]->Previous_month_1)/$barChart[1]->Previous_month_1)*100}}
    }, {
      name: '{{$barChart[0]->Previous_month_2}}',
      y: {{(($barChart[1]->Previous_month_2-$barChart[2]->Previous_month_2)/$barChart[1]->Previous_month_2)*100}}
    }, {
      name: '{{$barChart[0]->Previous_month_3}}',
      y: {{(($barChart[1]->Previous_month_3-$barChart[2]->Previous_month_3)/$barChart[1]->Previous_month_3)*100}}
    }, {
      name: '{{$barChart[0]->Previous_month_4}}',
      y: {{(($barChart[1]->Previous_month_4-$barChart[2]->Previous_month_4)/$barChart[1]->Previous_month_4)*100}}
    }, {
      name: '{{$barChart[0]->Previous_month_5}}',
      y: {{(($barChart[1]->Previous_month_5-$barChart[2]->Previous_month_5)/$barChart[1]->Previous_month_5)*100}}
    }]
  }]
});

</script>

@else

<script type="text/javascript">

Highcharts.chart('piechart', {
  chart: {
    plotBackgroundColor: null,
    plotBorderWidth: null,
    plotShadow: false,
    type: 'pie'
  },
  credits: {
      enabled: false
  },
  exporting: { enabled: false },
  title: {
    text: 'Percentage of vacancy in last 6 months'
  },
  tooltip: {
    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
  },
  plotOptions: {
    pie: {
      allowPointSelect: true,
      cursor: 'pointer',
      dataLabels: {
        enabled: true,
        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
        style: {
          color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
        }
      }
    }
  },
  series: [{
    name: 'Vacancy',
    colorByPoint: true,
    data: [{
      name: '{{$barChart[0]->Previous_month}}',
      y: 0,
      sliced: true,
      selected: true
    }, {
      name: '{{$barChart[0]->Previous_month_1}}',
      y: 0
    }, {
      name: '{{$barChart[0]->Previous_month_2}}',
      y: 0
    }, {
      name: '{{$barChart[0]->Previous_month_3}}',
      y: 0
    }, {
      name: '{{$barChart[0]->Previous_month_4}}',
      y: 0
    }, {
      name: '{{$barChart[0]->Previous_month_5}}',
      y: 0
    }]
  }]
});

</script>
@endif

@endsection