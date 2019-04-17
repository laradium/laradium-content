@extends($layout)
@section('content')
    @foreach($page->widgets() as $widget)
        {!! view($widget['view'], [
            'widget' => $widget['block']->block,
            'block' => $widget['block']
        ]) !!}
    @endforeach
@endsection