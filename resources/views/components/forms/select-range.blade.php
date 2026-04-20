<div id="ID-slider-{{ $filter }}"></div>
<input type="hidden" id="id-{{ $filter }}" name="{{ $name }}" value="{{ $value }}">
@push('scripts')
<script>
    layui.use(['jquery', 'slider'],function () {
        var slider_{{ $filter }} = layui.slider;
        let $ = layui.jquery;
        // 渲染
        slider_{{ $filter }}.render({
            elem: '#ID-slider-{{ $filter }}',
            value: {{ $value }},
            min: {{ $min }},
            max: {{ $max }},
            change: function (value) {
                $('#id-{{ $filter }}').val(value);
            }
        });
    });
</script>
@endpush
