<input type="checkbox" name="{{ $name }}" lay-filter="{{ $filter }}-filter" value="1" title="可用|停用" lay-skin="switch" @checked($value === \App\Enum\StatusSwitch::ENABLED)>
