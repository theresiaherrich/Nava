@props(['variant' => 'pending'])
@php $variant = in_array($variant, ['success','pending','error'], true) ? $variant : 'pending'; @endphp
<span {{ $attributes->class(['a3-status', 'a3-status--'.$variant]) }}>{{ $slot }}</span>
