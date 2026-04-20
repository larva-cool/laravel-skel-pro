<div id="ID-rate-{{ $filter }}"></div>
<input type="hidden" id="id-{{ $filter }}" name="{{ $name }}" value="{{ $value }}">
@push('scripts')
<script>
    layui.use(['jquery', 'rate'],function () {
        var rate_{{ $filter }} = layui.rate;
        let $ = layui.jquery;
        // 渲染
        rate_{{ $filter }}.render({
            elem: '#ID-rate-{{ $filter }}',
            value: {{ $value }},
            choose: function(value) {
                $('#id-{{ $filter }}').val(value);
            }
        });
    });
</script>
@endpush
