<select name="{{ $name }}" id="{{ $filter }}_select" lay-filter="{{ $filter }}-filter" @if($multiple) multiple @endif>
    @if($placeholder)
        <option value="">{{$placeholder}}</option>
    @endif
    @foreach($items as $key => $val)
        <option value="{{$key}}" @selected(in_array($key, $selected))>{{$val}}</option>
    @endforeach
</select>
