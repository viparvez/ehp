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

  <style type="text/css" href="{{url('/')}}/public/css/custom.css"></style>

</head>

<body>
    <div class="col-md-1 col-md-offset-10" style="padding: 50px;">
      <button class="btn btn-success" id="print"><i class="fa fa-print"> Print</i></button>
    </div>
    <div class="wrapper">
        <div class="col-md-12 col-xs-12 col-sm-12 text-center">
            <h4>CITY OF NEW YORK HUMAN RESOURCES ADMINISTRATION</h3>
            <h4 class="btn btn-success" style="border-radius: 0px; background-color: black; color: white">HASA FAMILY BILLING INVOICE</h4>
            <br><br>
        </div>

        <div class="col-md-6 col-xs-6 col-sm-6" style="font-size: 11px; padding-bottom: 30px">
            <table class="tableNew">
              <tbody>
                <tr>
                  <td>Building Code</td>
                  <td>: &nbsp; {{$bill_details[0]->Apartment->Floor->Facility->code}}</td>
                </tr>

                <tr>
                  <td>Building Name</td>
                  <td>: &nbsp; {{$bill_details[0]->Apartment->Floor->Facility->name}}</td>
                </tr>

                <tr>
                  <td>Address</td>
                  <td>: &nbsp;
                    @if(!empty($bill_details[0]->Apartment->Floor->Facility->city))
                      {{$bill_details[0]->Apartment->Floor->Facility->city}}, 
                    @else
                    @endif
                    
                    @if(!empty($bill_details[0]->Apartment->Floor->Facility->State->name))
                      {{$bill_details[0]->Apartment->Floor->Facility->State->name}},
                    @else
                    @endif
                    
                    @if(!empty($bill_details[0]->Apartment->Floor->Facility->zip))
                     {{$bill_details[0]->Apartment->Floor->Facility->zip}}
                    @else
                    @endif
                  </td>
                </tr>
              </tbody>
            </table>
        </div>

        <div class="col-md-5 col-md-offset-1 col-xs-5 col-sm-5 col-xs-offset-1 col-sm-offset-1" style="font-size: 11px">
            <table class="tableNew">
              <tbody>
                <tr>
                  <td>Billing Period</td>
                  <td>: &nbsp; {{$bill_details[0]->Billing->month}}, {{$bill_details[0]->Billing->year}}</td>
                </tr>

                <tr>
                  <td>Facility Rate</td>
                  <td>: &nbsp; ${{number_format($bill_details[0]->Billing->rate,2)}}</td>
                </tr>
              </tbody>
            </table>
        </div>
        <br><br>
        <div class="col-md-12 col-xs-12 col-sm-12">
            <table id="table" class="table table-bordered table-striped" style="font-size: 12px">
              <thead>
                  <th>SL</th>
                  <th>Apartment</th>
                  <th>Client</th>
                  <th>SSN</th>
                  <th>Move In Date</th>
                  <th>Move Out Date</th>
                  <th>Total Days</th>
                  <th>Amount</th>
              </thead>

              <tbody>
                  @php
                    $total_amount = 0;
                  @endphp
                  @foreach($bill_details as $k => $v)

                    @php
                      $total_amount += $v->amount;
                    @endphp

                      <tr>
                          <td>{{$k + 1}}</td>
                          <td>{{$v->Apartment->code}}</td>
                          <td>{{$v->Admission->Client->fname}} {{$v->Admission->Client->lname}}</td>
                          <td>
			      {{Utilities::my_simple_crypt($v->Client->ssn,'d')}}
			  </td>
                          <td>{{date('m-d-Y',strtotime($v->moveindate))}}</td>
                          <td>
                            @if(!empty($v->moveoutdate))
                              {{date('m-d-Y',strtotime($v->moveoutdate))}}
                            @else
                            @endif
                          </td>
                          <td>{{$v->total_days}}</td>
                          <td>${{number_format($v->amount,2)}}</td>
                      </tr>
                  @endforeach
                  <tr>
                      <td colspan="7" style="text-align: right; border: 0">Total payable:</td>
                      <td>${{number_format($total_amount,2)}}</td>
                  </tr>
              </tbody>
            </table>
        </div>

        <div class="col-md-12 col-xs-12 col-sm-12" id="footer">
            <div class="col-md-4 col-sm-4 col-xs-4 text-center">
                <b></b>
                <h5 style="text-decoration: overline;">Reviewed By</h5>
            </div>

            <div class="col-md-4 col-sm-4 col-xs-4 text-center">
                <b style="color: white"></b>
                <h5 style="text-decoration: overline;">Chief Financial Officer</h5>
            </div>

            <div class="col-md-4 col-sm-4 col-xs-4 text-center">
                <b style="color: white"></b>
                <h5 style="text-decoration: overline;">Program Manager</h5>
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
           loadCSS: ['http://localhost/ehp-laravel/public/LTE/bower_components/bootstrap/dist/css/bootstrap.min.css','http://localhost/ehp-laravel/public/css/custom.css'],            // load an additional css file - load multiple stylesheets with an array []
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