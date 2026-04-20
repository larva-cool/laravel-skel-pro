<input type="text" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}"
       lay-filter="{{ $filter }}-filter"
       @if ($required)
           required lay-verify="required"
       @endif

       autocomplete="off" class="layui-input">
