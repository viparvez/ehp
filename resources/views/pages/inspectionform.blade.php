@php
  if(!empty($code_helper)){
    $code1 = $code_helper->id + 1;
    $code = 'INS'.sprintf('%06d', $code1);
  }
  else{
    $code = 'INS000001';
  }
@endphp

<div class="alert alert-danger print-error-msg" style="display:none">
    <ul></ul>
</div>

<form id="inspections" action="{{route('inspections.store')}}" method="POST">

  {{csrf_field()}}

  <div class="col-md-12 col-xs-12 col-sm-12">

    <div class="col-md-4 col-xs-12 col-sm-6">
      <div class="form-group">
        <label>Date <code>*</code></label>
        <div class="input-group date">
          <div class="input-group-addon">
            <i class="fa fa-calendar"></i>
          </div>
          <input type="text" class="form-control pull-right" id="datepicker" name="date" value="{{date('m-d-Y')}}">
        </div>
      </div>

      <div class="form-group">
        <label>Cap Due Date <code>*</code></label>
        <div class="input-group date">
          <div class="input-group-addon">
            <i class="fa fa-calendar"></i>
          </div>
          @php
            $date = date('m-d-Y');
            $newDate = date('m-d-Y', strtotime("+15 days"));
          @endphp
          <input type="text" class="form-control pull-right" id="datepicker1" name="cap_due_date" value="{{$newDate}}" readonly="">
        </div>
      </div>

      <div class="form-group">
        <a href="" id="addFollowupButton" data-toggle="modal" data-target="#secondOrderPrev">Inspection to be followed</a>
        <h4 id="followup_code"></h4>
      </div>

    </div>

    <div class="col-md-4 col-xs-12 col-sm-6">

      <div class="form-group">
        <label>Inspection Code</label>
        <input class="form-control" type="text" name="inspection_code" readonly="" value="{{$code}}">
      </div>

      <div class="form-group">
       <label>Facility <code>*</code></label>
       <select id="facility_id" name="facility_id" class="form-control" required>
        <option value="">SELECT</option>
          @foreach($facilities as $k => $v)
          <option value="{{$v->id}}">{{$v->code}} - {{$v->name}}</option>
          @endforeach
       </select>
      </div>


    </div>

    <div class="col-md-4 col-xs-12 col-sm-6">

      <div class="form-group">
        <label>Total Inspected Area <code>*</code></label>
        <input class="form-control" type="text" name="total_inspeted_area">
      </div>

      <div class="form-group">
        <label>SELECT Unit</label>
        <select name="apartment_id" id="apartment_id" class="form-control">
          <option value="">SELECT</option>
        </select>
      </div>

    </div>

    <div class="col-md-4 col-xs-12 col-sm-12">
      <!-- <h4 class="text-center" style="color:orange">-- OR --</h4> -->
      <button class="btn btn-block btn-success" id='blankRow'>ADD NEW ROW</button><br><br>
    </div>
    
  </div>


  <table class="table tabl-condensed">
    <thead>
      <th>Inspection Content</th>
      <th>Category</th>  
      <th>Deficiency Details</th>
      <th>Concern</th>
      <th>Comment</th>
      <th>Action</th>
    </thead>

    <tbody id="formElements">
      
    </tbody>
  </table>


  <fieldset>
    <div class="col-md-6 col-sm-12">
      <div class="form-group">
        <label>Comments</label>
        <textarea name="comments" class="form-control" rows="4"></textarea>
      </div>
    </div>
    
    <div class="col-md-6 col-sm-12">
      <br>
      <div class="block" id="save">
        <span>COMPLETE</span>
        <input data-index="1" id="fast" type="checkbox" name="save" />
        <label for="fast"></label>
      </div>
      <br><br><br><br>
      <button class="btn btn-block btn-primary btn-sm" id="submit" type="submit">SUBMIT</button>
      <button class="btn btn-block btn-primary btn-sm" id="loading" style="display: none" disabled="">Working...</button>
    </div>

  </fieldset>
  
</form>

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

  var max_fields      = 100;
  var wrapper         = $("#formElements");
  var add_button      = $("#blankRow");
  var x               = 0;

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
                  $("#facility_id").attr("disabled", true);
              }
          });
      }
      else
      {
      alert('You Reached the limits')
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
    followup_code = null;
  }
</script>