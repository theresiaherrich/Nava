@props(['placeholder' => 'Cari…', 'param' => 'q'])

{{-- Pencarian sisi server (GET). Filter lain dipertahankan; terkirim otomatis 400 ms setelah berhenti mengetik. --}}
<form method="GET" role="search" {{ $attributes->class('relative') }}
      x-data="{ t: null }" x-on:input="clearTimeout(t); t = setTimeout(() => $el.requestSubmit(), 400)">
    @foreach (request()->except([$param, 'page']) as $k => $v)
        @if (is_string($v))
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endif
    @endforeach
    <x-ui.search-input :placeholder="$placeholder" :label="$placeholder" name="{{ $param }}" value="{{ request($param) }}" />
</form>
