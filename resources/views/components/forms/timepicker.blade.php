<input type="text" class="layui-input" id="ID-laydate-{{$filter}}" placeholder="yyyy-MM-dd">
@push('scripts')
<script>
    layui.use(['laydate'], function () {
        var laydate = layui.laydate;
        // 渲染
        laydate.render({
            elem: '#ID-laydate-{{$filter}}'
            ,type:'time'
            ,format: 'HH:mm:ss'
            ,trigger: 'click'
            ,value: '{{$value}}'
        });
    });
</script>
@endpush
