<div id="ID-colorpicker-{{ $name }}"></div>
<input type="hidden" id="id-{{ $name }}" name="{{ $name }}" lay-filter="{{ $filter }}" value="{{ $value }}">

@push('scripts')
<script>
    layui.use(['jquery', 'colorpicker'], function() {
        var colorpicker_{{ $name }} = layui.colorpicker;
        var $ = layui.jquery;
        // 渲染
        colorpicker_{{ $name }}.render({ // eg1
            elem: '#ID-colorpicker-{{ $name }}', // 绑定元素
            color: '{{ $value }}', // 设置默认色
            change: function(color){ // 颜色改变的回调
                $('#id-{{ $name }}').val(color);
            }
        });
    });
</script>
@endpush
