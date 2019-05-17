@foreach($clients as $client)
  <div>
      <h3>
          <a href="">{{$client->fname }}</a>
      </h3>
  </div>
@endforeach

{{ $clients->links() }}