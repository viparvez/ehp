<div class="alert alert-danger print-error-msg" style="display:none">
    <ul></ul>
</div>

<form id='inspections' action='{{route('inspections.update',$inspection->id)}}' method='POST'>

  {{csrf_field()}}

  @php 
    $inspectionDate = (new \App\Custom\Custom)->dateToView($inspection->date, "m-d-Y");
    $cap_due_date = (new \App\Custom\Custom)->dateToView($inspection->cap_due_date, "m-d-Y");
  @endphp

  <input type="hidden" name="_method" value="PUT">

  <div class='col-md-12 col-xs-12 col-sm-12'>

    <div class="col-md-4 col-xs-12 col-sm-6">
      <div class='form-group'>
        <label>Date <code>*</code></label>
        <div class='input-group date'>
          <div class='input-group-addon'>
            <i class='fa fa-calendar'></i>
          </div>
          <input type='text' class='form-control pull-right' id='datepicker1' name='date' value='{{$inspectionDate}}'>
        </div>
      </div>

      <div class='form-group'>
        <label>Cap Due Date <code>*</code></label>
        <div class='input-group date'>
          <div class='input-group-addon'>
            <i class='fa fa-calendar'></i>
          </div>
          @php
            $date = date('Y-m-d');
            $newDate = date('Y-m-d', strtotime('+15 days'));
          @endphp
          <input type='text' class='form-control pull-right calender' id='datepicker1' name='cap_due_date' value='{{$cap_due_date}}' readonly=''>
        </div>
      </div>

      @if(empty($inspection->followedins_id))
        <a href='' id='addFollowupButton' data-toggle='modal' data-target='#secondOrderPrev' >Inspection to be followed</a>
        <h4 id='followup_code'></h4>
      @else
        <a href='' id='addFollowupButton' data-toggle='modal' data-target='#secondOrderPrev' hidden>Inspection to be followed</a>

        <h4 id='followup_code'>
          Following Inspection: <a href='#'>{{$inspection->Followed->code}}</a>&nbsp;&nbsp;&nbsp; <span id='followup_id' hidden>{{$inspection->followedins_id}}</span>
        </h4>
      @endif

    </div>

    <div class="col-md-4 col-xs-12 col-sm-6">

      <div class='form-group'>
        <label>Inspection Code</label>
        <input class='form-control' type='text' name='inspection_code' readonly='' value='{{$inspection->code}}'>
      </div>

      <div class='form-group'>
       <label>Facility <code>*</code></label>
       <select id='facility_id' name='facility_id' class='form-control' disabled="">
        <option value=''>SELECT</option>
          @foreach($facilities as $k => $v)
            @if($v->id == $inspection->facility_id) 
              <option value='{{$v->id}}' selected>{{$v->name}}</option>
            @else 
              <option value='{{$v->id}}'>{{$v->name}}</option>
            @endif
          @endforeach
       </select>
      </div>

    </div>


    <div class="col-md-4 col-xs-12 col-sm-6">

      <div class='form-group'>
        <label>Total Inspected Area <code>*</code></label>
        <input class='form-control' type='text' name='total_inspeted_area' value="{{$inspection->total_inspected_area}}">
      </div>

      <div class='form-group'>
        <label>SELECT Apartment</label>
        <select name='apartment_id' id='apartment_id' class='form-control'>
          <option value=''>SELECT</option>
          @foreach($apartments as $ap)
            <option value="{{$ap->id}}">{{$ap->name}}</option>
          @endforeach
        </select>
      </div>

    </div>

    <div class="col-md-4 col-xs-12 col-sm-12">
      <button class='btn btn-block btn-success' id='blankRow'>ADD NEW ROW</button><br><br>
    </div>
    
  </div>


  <table class='table tabl-condensed'>
    <thead>
      <th>Inspection Content</th>
      <th>Category</th>  
      <th>Deficiency Details</th>
      <th>Concern</th>
      <th>Comment</th>
      <th>Action</th>
    </thead>

    <tbody id='formElements'>
      @foreach($inspection_details as $k => $insdet)
        @php
          $key = $k+1;
        @endphp
        <span id="key" hidden>{{$key}}</span>
        <tr id='tr{{$key}}'>
          <td>
            <input type='text' class='form-control' name='content[]' value="{{$insdet->content}}">
            <input type='hidden' name='apartment_id[]' value='{{$insdet->apartment_id}}'>
          </td>
          <td>
            <select onchange='getDef(this.value,"{{$key}}")' name='category_id[]' class='form-control'>
            <option value=''>SELECT</option>
              @foreach($categories as $cat)
                @php
                  $catselected = '';
                  if($cat->id == $insdet->Deficiencydetail->Category->id) {
                    $catselected = 'selected';
                  }
                @endphp
                <option value="{{$cat->id}}" {{$catselected}}>{{$cat->name}}</option>
              @endforeach
            </select>
          </td>
          <td>
            <select id='details{{$key}}' name='concern_id[]' class='form-control' onchange='getCon(this.value,"{{$key}}")'>
              <option value="{{$insdet->concern_id}}" selected>{{$insdet->Deficiencydetail->description}}</option>
            </select>
          </td>
          <td>
            <h4 class='text-center' id='concern{{$key}}'>{{$insdet->Deficiencydetail->Concern->name}}</h4>
          </td>
          <td>
            <input class='form-control' type='hidden' id='weightage{{$key}}' name='weightage[]' value="{{$insdet->Deficiencydetail->id}}" readonly>
            <input type='text'  class='form-control' name='comment[]' id='comment{{$key}}' value='{{$insdet->comments}}'>
          </td>
          <td>
            <button class='btn btn-sm btn-danger delete'>DELETE</button>
          </td> 
        </tr>
      @endforeach
      
      @php
        $x = count($inspection_details);
      @endphp
      <span id="x" hidden>{{$x}}</span>
    </tbody>
  </table>


  <fieldset>
    <div class='col-md-6 col-sm-12'>
      <div class='form-group'>
        <label>Comments</label>
        <textarea name='comments' class='form-control' rows='4'>{{$inspection->comments}}</textarea>
      </div>
    </div>
    
    <div class='col-md-6 col-sm-12'>
      <br>
      <div class='block' id='save'>
        <span>COMPETE</span>
        <input data-index='1' id='fast' type='checkbox' name='save' />
        <label for='fast'></label>
      </div>
      <br><br><br><br>
      <button class='btn btn-block btn-primary btn-sm' id='submitEdit' type='submit'>SUBMIT</button>
      <button class='btn btn-block btn-primary btn-sm' id='loading' style='display: none' disabled=''>Working...</button>
    </div>

  </fieldset>
  
</form>

<script type="text/javascript">
  $(function () {
    //Date picker
    $('.calender').datepicker({
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

  var max_fields      = 200;
  var wrapper         = $("#formElements");
  var add_button      = $("#blankRow");
  var x               = $("#x").text();

  $("#showcontent").on('click','#blankRow',function(e){
    e.preventDefault();
      if(x < max_fields){
          x++;
          $.ajax({
              dataType: "json",
              url: "{{url('/')}}/inspections/formfield/"+x+"/false",
              success: function (data) {
                  $("#formElements").prepend(JSON.stringify(data));
              }
          });
      }
      else
      {
      alert('You Reached the limits')
      }
  });

  $("#showcontent").on('change','#apartment_id',function(e){
    e.preventDefault();
      if(x < max_fields){
          x++;
          $.ajax({
              dataType: "json",
              url: "{{url('/')}}/inspections/formfield/"+x+"/"+this.value,
              success: function (data) {
                  $("#formElements").prepend(JSON.stringify(data));
                  $("#apartment_id option").prop("selected", false);
              }
          });
      }
      else
      {
      alert('You Reached the limits!')
      }
  });

  $('#formElements').on("click",".delete", function(e){
      e.preventDefault(); 
      $(this).parent().parent().remove();
      //x--;
  })

  function removeFollowup() {
    $('#followup_code').html("");
    $('#addFollowupButton').show();
  }
</script>