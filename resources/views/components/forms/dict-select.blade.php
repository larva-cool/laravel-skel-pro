<select name="{{ $name }}" id="{{ $filter }}-select" lay-filter="{{ $filter }}-filter">
    @if($placeholder)
    <option value="">{{$placeholder}}</option>
    @endif
    @foreach($items as $key=>$name)
        <option value="{{$key}}" @selected($key == $selected)>{{$name}}</option>
    @endforeach
</select>
