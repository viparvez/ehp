<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ config('app.name', 'EHMP') }}</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="{{url('/')}}/public/LTE/dist/css/skins/_all-skins.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

  <script type="text/javascript">
   window.onload = function() { window.print(); }
  </script>
</head>

<body>
    <div class="wrapper">
        <div class="col-md-12 col-xs-12 col-sm-12 text-center">
            <h3>CITY OF NEW YORK HUMAN RESOURCES ADMINISTRATION</h3>
            <h4><u>Inspection Outcome Report</u></h4>
            <p>Facility Name: &nbsp; {{$inspection->Facility->name}}</p>
            <p>Facility Address: &nbsp; {{$inspection->Facility->address}}</p>
        </div>

        <div class="col-md-12 col-xs-12 col-sm-12">
            <p>Inspection date: &nbsp; <b>{{$inspection->date}}</b></p>
            <p>Last updated at: &nbsp; <b>{{$inspection->updated_at}}</b></p>
        </div>

        <div class="col-md-12 col-xs-12 col-sm-12">
            <table class="table table-bordered table-striped">
                <thead>
                    <th>SL</th>
                    <th>Apartment</th>
                    <th>Deficiency</th>
                    <th>Concern</th>
                    <th>Comment</th>
                    <th>Weightage</th>
                    <th>Corrective Action Plan</th>
                </thead>

                <tbody>
                    @foreach($inspection->Inspectiondetail as $k => $v)
                        <tr>
                            <td>{{$k + 1}}</td>
                            <td>{{$v->content}}</td>
                            <td></td>
                            <td>{{$v->Deficiencydetail->Concern->name}}</td>
                            <td>{{$v->comment}}</td>
                            <td>{{number_format($v->Deficiencydetail->weightage,2)}}</td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-md-12 col-xs-12 col-sm-12">
              @php
                $totalScore = 0;
                $avgScore = 0;
              @endphp

              @foreach($inspection->Inspectiondetail as $k => $v)
                @php
                  $totalScore += $v->Deficiencydetail->weightage;
                @endphp
              @endforeach

              @php
                $avgScore = $totalScore/count($inspection->Inspectiondetail,0);
              @endphp

            <h5>Total Number Of Inspection Area Inspected : <b>{{$inspection->total_inspected_area}}</b></h5>
            <h5>Weighted Average Deficiencies Per Inspected Area with Deficiencies : <b>{{number_format($avgScore,2)}}</b></h5>

        </div>

        <div class="col-md-12 col-xs-12 col-sm-12" style="padding-bottom: 100px">
            <div class="col-md-6 col-xs-6 col-sm-6">
                <h4>Outcome: 
                    <b>
                        @if($avgScore <= 0)
                            Very Good
                        @elseif($avgScore > 0 && $avgScore <= 3.0)
                            Good
                        @elseif($avgScore > 3.0 && $avgScore<= 5.0)
                            Satisfactory
                        @elseif($avgScore > 5.0 && $avgScore <= 10.0)
                            Unsatisfactory
                        @else
                            Unacceptable
                        @endif
                    </b>
                </h4>
            </div>

            <div class="col-md-6 col-xs-6 col-sm-6">
                <h4>Corrective Action Plan:</h4>
            </div>
        </div>

        <style type="text/css">
            #table tr td {
                border: 1px solid black;
            }
        </style>

        <div class="col-md-12 col-xs-12 col-sm-12" style="padding-bottom: 100px">
            <div class="col-md-3 col-sm-3 col-xs-3">
                <table class="table table-bordered text-center" id="table">
                    <tr>
                        <td colspan="2">Rating</td>
                    </tr>
                    <tr>
                        <td>Very Good</td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td>Good</td>
                        <td>0.01-3.0</td>
                    </tr>
                    <tr>
                        <td>Satisfactory</td>
                        <td>3.01-5.0</td>
                    </tr>
                    <tr>
                        <td>Unsatisfactory</td>
                        <td>5.01-10.0</td>
                    </tr>
                    <tr>
                        <td>Unacceptable</td>
                        <td>>10</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="col-md-12 col-xs-12 col-sm-12">
            <div class="col-md-4 col-sm-4 col-xs-4 text-center">
                <b>{{$inspection->Updatedby->name}}</b>
                <h5 style="text-decoration: overline;">Inspected By</h5>
            </div>

            <div class="col-md-4 col-sm-4 col-xs-4 text-center">
                <b>John Doe</b>
                <h5 style="text-decoration: overline;">Program Manager</h5>
            </div>

            <div class="col-md-4 col-sm-4 col-xs-4 text-center">
                <b>Kubaba</b>
                <h5 style="text-decoration: overline;">Program Director</h5>
            </div>
        </div>

    </div>
</body>
</html>