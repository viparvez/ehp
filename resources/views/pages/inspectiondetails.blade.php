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

</head>

<body>
    <div class="col-md-1 col-md-offset-10" style="padding: 50px;">
      <button class="btn btn-success" id="print"><i class="fa fa-print"> Print</i></button>
    </div>
    <div class="wrapper">
        <div class="col-md-12 col-xs-12 col-sm-12 text-center">
            <h4>CITY OF NEW YORK HUMAN RESOURCES ADMINISTRATION</h3>
            <h4>Emergency Housing Management Program</h4>
            <h4><u>Inspection Outcome Report</u></h4>
            <p>Facility Name: &nbsp; {{$inspection->Facility->name}}</p>
            <p>Facility Address: &nbsp; 
                @if(!empty($inspection->Facility->city))
                  {{$inspection->Facility->city}}, 
                @else
                @endif
                
                @if(!empty($inspection->Facility->State->name))
                  {{$inspection->Facility->State->name}},
                @else
                @endif
                
                @if(!empty($inspection->Facility->zip))
                 {{$inspection->Facility->zip}}
                @else
                @endif
            </p>
        </div>

        <div class="col-md-12 col-xs-12 col-sm-12">
            <table class="table">
              <tbody>
                <tr>
                  <td>Inspection Code: &nbsp; <b>{{$inspection->code}}</b> <br>Inspection Type: &nbsp;
		   <b>@if(!empty($inspection->Followed))
			FOLLOWUP
                      @else
                        INITIAL
                      @endif
		   </b></td>
                  <td style="text-align: center;">Inspection Date: &nbsp; <b>{{(new \App\Custom\Custom)->dateToView($inspection->date, "m-d-Y")}}</b></td>
                  <td style="text-align: right">Inspector: &nbsp; <b>{{$inspection->CreatedBy->name}}</b></td>
                </tr>
              </tbody>
            </table>

            <p style="text-align: center;"><b><u>DEFICIENCY DETAILS REPORT</u></b></p>
        </div>

        <div class="col-md-12 col-xs-12 col-sm-12">
            <table id="table" class="table table-bordered table-striped">
              <thead>
                  <th>SL</th>
                  <th>Apartment</th>
                  <th>Deficiency Concern</th>
                  <th>Deficiency Category</th>
                  <th>Deficiency Details</th>
                  <th>Comment</th>
                  <th>Weightage</th>
                  <th>Corrective Action Plan</th>
              </thead>

              <tbody>
                  @foreach($inspection->Inspectiondetail as $k => $v)
                      <tr>
                          <td>{{$k + 1}}</td>
                          <td>{{$v->content}}</td>
                          <td>{{$v->Deficiencydetail->Concern->name}}</td>
                          <td>{{$v->Deficiencydetail->Category->name}}</td>
                          <td>{{$v->Deficiencydetail->description}}</td>
                          <td>{{$v->comments}}</td>
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

              <style type="text/css">
                .newTable {
                  border-collapse: collapse;
                  width: 100%;
                  border: 1px solid black;
                  font-size: 12px;
                } 

                .newTable tr td {
                  border: 1px solid #bbb;
                  padding: 5px;
                  text-align: center;
                }
              </style>
            <h1 style="page-break-after:always"></h1>
            <u><h3 style="text-align: center;">Outcome of Inspection: <b id="code">{{$inspection->code}}</b></h3></u><hr>
            <h5 style="text-align: center"><b>CURRENT DEFICIENCIES</b></h5>
            <table border="1" class="newTable">
              <tbody>
                <tr>
                  <td><b>Weighted Deficiencies</b></td>
                  @php
                    $total_deficiency_count = 0;
                  @endphp
                  @foreach($groupByConcern as $gbc)
                    <td><b>{{$gbc->concern}}</b></td>
                    @php
                      $total_deficiency_count += $gbc->concern_count;
                    @endphp
                  @endforeach
                  <td><b>Total Deficiencies</b></td>
                </tr>

                <tr>
                  <td>{{$totalScore}}</td>
                  @foreach($groupByConcern as $gbc)
                    <td>{{$gbc->concern_count}}</td>
                  @endforeach
                  <td>{{$total_deficiency_count}}</td>
                </tr>
              </tbody>
            </table>
            <br><br>

            <style type="text/css">

              #t2 {
                width: 100%;
                font-size: 12px;
              }

              #t2 tr td {
                padding: 10px;
              }

              #t2 th {
                padding: 8px;
                text-align: center;
              }

            </style>

            @php
              $avgScore = (($totalScore/$total_deficiency_count) + ($totalScore/$inspection->total_inspected_area))/2;
            @endphp

        </div>

	<div class="col-md-6 col-xs-6 col-sm-6">
            <table border="1" id="t2">
              <tr>
                <td>Total Number Of Inspection Area Inspected :</td>
                <td><b>{{$inspection->total_inspected_area}}</b></td>
              </tr>
              <tr>
                <td>Weighted Average Deficiencies Per Inspected Area with Deficiencies</td>
                <td><b>{{number_format($avgScore,2)}}</b></td>
              </tr>
            </table>
	</div>


	<div class="col-md-6 col-xs-6 col-sm-6">
                <table border="1" id="t2">
                  <tbody>
                    <tr>
                      <td height="100%"><b>COMMENTS/NOTE:</b><br>{{$inspection->comments}}</td>
                    </tr>
                  </tbody>
                </table>
         </div>


        <div class="col-md-12 col-xs-12 col-sm-12" style="padding-top: 50px">
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

                <table class="newTable">
                    <tr>
                        <td colspan="2"><b>Rating</b></td>
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

            <div class="col-md-6 col-xs-6 col-sm-6">
                <h4><b>Corrective Action Plan:</b></h4>
                <table border="1" class="newTable">
                  <tbody>
                    <tr>
                      <td height="165"></td>
                    </tr>
                  </tbody>
                </table>
            </div>

        </div>

        <div class="col-md-12 col-xs-12 col-sm-12" style="padding-top: 330px">
            <div class="col-md-4 col-sm-4 col-xs-4 text-center">
                <b></b>
                <h5 style="text-decoration: overline;">Inspected By</h5>
            </div>

            <div class="col-md-4 col-sm-4 col-xs-4 text-center">
                <b style="color: white"></b>
                <h5 style="text-decoration: overline;">Program Manager</h5>
            </div>

            <div class="col-md-4 col-sm-4 col-xs-4 text-center">
                <b style="color: white"></b>
                <h5 style="text-decoration: overline;">Program Director</h5>
            </div>
        </div>
    </div>


   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
   <script type="text/javascript" src="{{url('/')}}/public/js/printThis.js"></script>

   <script type="text/javascript">
     $('#print').click(function(){
       $(".wrapper").printThis({

           debug: false,           // show the iframe for debugging
           importCSS: true,        // import parent page css
           importStyle: true,     // import style tags
           printContainer: true,   // print outer container/$.selector
           loadCSS: "http://localhost/ehp-laravel/public/LTE/bower_components/bootstrap/dist/css/bootstrap.min.css",            // load an additional css file - load multiple stylesheets with an array []
           pageTitle: null,          // add title to print page
           removeInline: false,    // remove all inline styles
           printDelay: 333,        // variable print delay
           header: null,           // prefix to html
           footer: null,           // postfix to html
           formValues: true,       // preserve input/form values
           canvas: false,          // copy canvas content (experimental)
           base: false,            // preserve the BASE tag, or accept a string for the URL
           doctypeString: '<!DOCTYPE html>', // html doctype
           removeScripts: false,   // remove script tags before appending
           copyTagClasses: true   // copy classes from the html & body tag

       });
     })
   </script>

</body>
</html>