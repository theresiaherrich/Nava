@props(['title' => null])
<section {{ $attributes->class(['a3-card']) }}>
    @if($title)<h2 class="text-base font-bold text-slate-900 mb-3">{{ $title }}</h2>@endif
    {{ $slot }}
</section>
