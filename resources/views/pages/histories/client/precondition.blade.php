
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