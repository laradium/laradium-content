@extends($layout)
@section('content')
    @foreach($page->widgets() as $widget)
        {!! $widget !!}
    @endforeach
@endsection