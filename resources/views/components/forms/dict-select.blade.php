<select name="{{ $name }}" id="{{ $filter }}-select" lay-filter="{{ $filter }}-filter">
    @if($placeholder)
    <option value="">{{$placeholder}}</option>
    @endif
    @foreach($items as $item)
        <option value="{{$item['value']}}" @selected($item['value'] == $selected)>{{$item['name']}}</option>
    @endforeach
</select>
