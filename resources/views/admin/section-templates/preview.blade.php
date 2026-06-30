<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Önizleme — {{ $sectionTemplate->name }}</title>

    @if($theme && ! empty($theme->assets_json['css']))
        @foreach($theme->assets_json['css'] as $cssUrl)
            <link rel="stylesheet" href="{{ $cssUrl }}">
        @endforeach
    @else
        {{-- Minimal fallback: Bootstrap 5 --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    @endif

    <style>
        /* Önizleme bandı artık sayfayı KAYDIRMAZ — body padding yok, yüzen rozet. */
        body { margin: 0; }
        .cms-preview-bar {
            position: fixed; bottom: 12px; left: 12px; z-index: 2147483000;
            background: #1e1b4b; color: #c7d2fe;
            font-family: ui-monospace, monospace; font-size: 11px;
            padding: 6px 10px; border-radius: 8px;
            box-shadow: 0 6px 20px rgba(0,0,0,.35);
            display: flex; gap: 8px; align-items: center; max-width: 92vw;
        }
        .cms-preview-bar strong { color: #a5b4fc; }
        .cms-pv-close { cursor: pointer; color: #a5b4fc; font-weight: bold; padding: 0 2px 0 6px; }
        .cms-pv-reopen {
            position: fixed; bottom: 12px; left: 12px; z-index: 2147483000;
            width: 26px; height: 26px; border-radius: 50%;
            background: #1e1b4b; color: #a5b4fc; cursor: pointer; display: none;
            align-items: center; justify-content: center;
            box-shadow: 0 4px 14px rgba(0,0,0,.3); font: 13px ui-monospace, monospace;
        }
    </style>
</head>
<body>

<div class="cms-preview-bar" id="cmsPvBar">
    <strong>CMS Önizleme</strong>
    <span>{{ $sectionTemplate->name }}</span>
    @if($theme)
        <span>· {{ $theme->name }}</span>
    @endif
    <span>· default_content_json ile render</span>
    <span class="cms-pv-close" title="Bandı kapat" onclick="cmsPvHide()">×</span>
</div>
<div class="cms-pv-reopen" id="cmsPvReopen" title="Önizleme bilgisini göster" onclick="cmsPvShow()">ⓘ</div>
<script>
    function cmsPvHide(){ var b=document.getElementById('cmsPvBar'),r=document.getElementById('cmsPvReopen'); if(b)b.style.display='none'; if(r)r.style.display='flex'; try{localStorage.setItem('cms_pv_bar_hidden','1');}catch(e){} }
    function cmsPvShow(){ var b=document.getElementById('cmsPvBar'),r=document.getElementById('cmsPvReopen'); if(b)b.style.display='flex'; if(r)r.style.display='none'; try{localStorage.removeItem('cms_pv_bar_hidden');}catch(e){} }
    try{ if(localStorage.getItem('cms_pv_bar_hidden')==='1'){ cmsPvHide(); } }catch(e){}
</script>

{!! $rendered !!}

@if($theme && ! empty($theme->assets_json['js']))
    @foreach($theme->assets_json['js'] as $jsUrl)
        <script src="{{ $jsUrl }}"></script>
    @endforeach
@else
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endif

</body>
</html>
