<textarea name="{{ $name }}" placeholder="{{ $placeholder }}"
          @if ($required)
              required lay-verify="required"
          @endif
          lay-filter="{{ $filter }}-filter" class="layui-textarea">{!! $value !!}</textarea>
