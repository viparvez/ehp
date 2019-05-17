
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