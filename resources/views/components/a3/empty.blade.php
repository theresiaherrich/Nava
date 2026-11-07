@props(['title' => 'Belum ada data'])
<div {{ $attributes->class(['a3-empty']) }}><strong class="block text-slate-700 mb-1">{{ $title }}</strong><div>{{ $slot }}</div></div>
