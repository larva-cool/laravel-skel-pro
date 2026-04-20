@pushOnce('head', 'aieditor')
<link rel="stylesheet" href="{{asset('admin/component/aieditor/style.css')}}">
@endPushOnce

<div id="{{ $filter }}" style="height: {{$height}};"></div>

@push('scripts')
<script type="module">
        import {AiEditor} from '{{asset('admin/component/aieditor/index.js')}}';
        const aiEditor_{{ $filter }} = new AiEditor({
            element: "#{{ $filter }}",
            placeholder: "{{ $placeholder }}",
            content: '{!! $value !!}',
            image: {
                allowBase64: false,
                uploadUrl: "{{route('admin.uploader.aieditor-image')}}",
                uploadFormName: "file",
            },
            video: {
                uploadUrl: "{{route('admin.uploader.aieditor-video')}}",
                uploadFormName: "file",
            },
            attachment: {
                uploadUrl: "{{route('admin.uploader.aieditor-file')}}",
                uploadFormName: "file",
            },
            ai: {
                models: {
                    openai: {
                        customUrl: "{{settings('openai.base_uri')}}",
                        model: "doubao-seed-1-6-250615",
                        apiKey: "{{settings('openai.api_key')}}"
                    }
                }
            },
            onCreated:(aiEditor)=>{
                window.{{ $name }}Body = aiEditor.getHtml();
            },
            onChange:(aiEditor)=>{
                window.{{ $name }}Body = aiEditor.getHtml();
            }
        })
</script>
@endpush
