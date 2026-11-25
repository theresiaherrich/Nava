@props(['id', 'data'])

{{-- Data halaman untuk Alpine, dibaca dengan JSON.parse(document.getElementById(id).textContent). --}}
<script type="application/json" id="{{ $id }}">@json($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)</script>
