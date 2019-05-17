@foreach($admissionhistories as $k => $admhist)
  <tr>
    <td>{{$admhist->admissionid}}</td>
    <td>{{$admhist->action}}</td>
    <td>{{$admhist->comment}}</td>
    <td>{{$admhist->UpdatedBy->name}}</td>
    <td>{{$admhist->updated_at}}</td>
  </tr>
@endforeach
  <tr>
    <td colspan="5">
      <span style="font-size: 10px">{{ $admissionhistories->links() }}</span>
    </td>
  </tr>