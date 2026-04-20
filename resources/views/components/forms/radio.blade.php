@foreach($items as $key => $val)
    <input type="radio" name="{{$name}}" value="{{$key}}" lay-filter="{{$filter}}-filter"  title="{{$val}}" @checked($selected == $key)>
@endforeach
