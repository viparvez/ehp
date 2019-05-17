<div class='nav-tabs-custom'>
  <ul class='nav nav-tabs'>
    <li class="active"><a href='#admission_history' data-toggle='tab'>Admission</a></li>
    <li><a href='#transfer_history' data-toggle='tab'>Transfer</a></li>
    <li><a href='#prec_change_history' data-toggle='tab'>Precondition Changes</a></li>
  </ul>
  <div class='tab-content'>
    
    <div class='active tab-pane' id='admission_history'>

      @if(!empty($admissionhistories))
        <table class="table table-striped">
          <thead>
            <th>Admission Code</th>
            <th>Action</th>
            <th>Comment</th>
            <th>#Updated by</th>
            <th>#Updated at</th>
          </thead>

          <tbody id="load_1">
            @foreach($admissionhistories as $k => $admhist)
              <tr>
                <td>{{$admhist->admissionid}}</td>
                <td>{{$admhist->action}}</td>
                <td>{{$admhist->comment}}</td>
                <td>{{$admhist->name}}</td>
                <td>{{$admhist->updated_at}}</td>
              </tr>
            @endforeach
              <tr>
                <td colspan="5">
                  <span style="font-size: 10px">{{ $admissionhistories->links() }}</span>
                </td>
              </tr>
          </tbody>
        </table>
      @else
        No history
      @endif
      
    </div>


    <div class='tab-pane' id='transfer_history'>

      @if(!empty($clienttransferhistories))
        <table class="table table-striped">
          <thead>
            <th>Admission Code</th>
            <th>Facility</th>
            <th>Transferred From</th>
            <th>Transferred To</th>
            <th>Comment</th>
            <th>#Updated by</th>
            <th>#Updated at</th>
          </thead>

          <tbody id="load_2">
            @foreach($clienttransferhistories as $k => $cltransfer)
              <tr>
                <td>{{$cltransfer->Admission->admissionid}}</td>
                <td>{{$cltransfer->prev_apt->Floor->Facility->code}} - {{$cltransfer->prev_apt->Floor->Facility->name}}</td>
                <td>{{$cltransfer->prev_apt->name}}</td>
                <td>{{$cltransfer->new_apt->name}}</td>
                <td>{{$cltransfer->comment}}</td>
                <td>{{$cltransfer->UpdatedBy->name}}</td>
                <td>{{$cltransfer->updated_at}}</td>
              </tr>
            @endforeach
              <tr>
                <td colspan="5">
                  <span style="font-size: 10px">{{ $clienttransferhistories->links() }}</span>
                </td>
              </tr>
          </tbody>
        </table>
      @else
        No history
      @endif
      
    </div>


    <div class='tab-pane' id='prec_change_history'>
      
      @if(!empty($preconditionchanges))
        <table class="table table-striped">
          <thead>
            <th>Precondition / Status</th>
            <th>Comment</th>
            <th>Updated by</th>
            <th>Updated at</th>
          </thead>

          <tbody id="load_3">
            @foreach($preconditionchanges as $k => $prec)
              <tr>
                <td>{{$prec->Precondition->name}}</td>
                <td>{{$prec->comment}}</td>
                <td>{{$prec->UpdatedBy->name}}</td>
                <td>{{$prec->updated_at}}</td>
              </tr>
            @endforeach
              <tr>
                <td colspan="5">
                  <span style="font-size: 10px">{{ $preconditionchanges->links() }}</span>
                </td>
              </tr>
          </tbody>
        </table>
      @else
        No history
      @endif

    </div>

  </div>
</div>