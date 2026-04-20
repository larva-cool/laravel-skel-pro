<div id="ID-laydate-{{$filter}}-range">
    <div class="layui-input-inline">
        <input type="text" autocomplete="off" name="{{$name}}_start_time" value="{{$startValue}}" id="ID-laydate-{{$filter}}-start-date" class="layui-input"
               placeholder="开始时间">
    </div>
    <div class="layui-form-mid">-</div>
    <div class="layui-input-inline">
        <input type="text" autocomplete="off" name="{{$name}}_end_time" value="{{$endValue}}" id="ID-laydate-{{$filter}}-end-date" class="layui-input"
               placeholder="结束时间">
    </div>
</div>
@push('scripts')
<script>
    layui.use(['laydate'], function () {
        var laydate = layui.laydate;
        // 渲染
        laydate.render({
            elem: '#ID-laydate-{{$filter}}-range',
            range: ['#ID-laydate-{{$filter}}-start-date', '#ID-laydate-{{$filter}}-end-date'],
            rangeLinked: true ,
            type: 'datetime'
        });
    });
</script>
@endpush
