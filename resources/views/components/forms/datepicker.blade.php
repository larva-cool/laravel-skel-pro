<input type="text" class="layui-input" id="ID-laydate-{{$filter}}" lay-filter="{{ $filter }}" placeholder="yyyy-MM-dd">
@push('scripts')
<script>
    layui.use(['laydate'], function () {
        var laydate = layui.laydate;
        // 渲染
        laydate.render({
            elem: '#ID-laydate-{{$filter}}'
            ,format: 'yyyy-MM-dd'
            ,trigger: 'click'
            ,value: '{{$value}}'
        });
    });
</script>
@endpush
