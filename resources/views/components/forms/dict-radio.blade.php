@foreach($items as $key=>$val)
    <input type="radio" name="{{ $name }}" id="{{ $filter }}-radio" value="{{$key}}" title="{{$val}}" @checked($key == $selected)>
@endforeach
