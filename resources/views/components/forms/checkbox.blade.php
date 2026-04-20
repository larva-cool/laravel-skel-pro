@foreach($items as $key => $val)
    <input type="checkbox" name="{{ $name }}" lay-filter="{{ $filter }}-filter" value="{{$key}}" title="{{$val}}"
           @checked(in_array($key, $selected))>
@endforeach

