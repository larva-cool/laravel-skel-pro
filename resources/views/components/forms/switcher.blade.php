<input type="checkbox" name="{{ $name }}" lay-filter="{{ $filter }}-filter" value="1" title="{{$title}}" lay-skin="switch" @checked($value === \App\Enums\StatusSwitch::ENABLED)>
