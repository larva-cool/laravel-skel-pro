@foreach($items as $key=>$val)
    <input type="radio" name="{{ $name }}" value="{{$key}}"  id="{{ $filter }}-radio" lay-filter="{{$filter}}-filter" title="{{$val}}" @checked($key == $selected)>
@endforeach
