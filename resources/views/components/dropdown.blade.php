@props([
    'name',
    'value' => '',
    'options' => [],
    'autosubmit' => false,
])

@php
    $nilai    = (string) $value;
    $terpilih = $options[$nilai] ?? (array_values($options)[0] ?? '');
@endphp

@once
<style>
    .dd { position: relative; display: inline-block; font-family: "Plus Jakarta Sans", "Segoe UI", system-ui, sans-serif; }

    .dd-trigger {
        height: 32px; display: inline-flex; align-items: center; gap: 10px;
        padding: 0 10px 0 12px; font-family: inherit; font-size: 11px; font-weight: 500;
        color: #0f172a; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04); cursor: pointer; white-space: nowrap;
        transition: border-color .15s, box-shadow .15s;
    }
    .dd-trigger:hover { border-color: #cbd5e1; }
    .dd-trigger:focus-visible { outline: none; border-color: #38bdf8; box-shadow: 0 0 0 3px #e0f2fe; }
    .dd.is-open .dd-trigger { border-color: #38bdf8; box-shadow: 0 0 0 3px #e0f2fe; }

    .dd-chev { width: 12px; height: 12px; color: #64748b; transition: transform .15s; flex-shrink: 0; }
    .dd.is-open .dd-chev { transform: rotate(180deg); }

    .dd-menu {
        display: none; position: absolute; top: calc(100% + 6px); left: 0; z-index: 40;
        min-width: 100%; padding: 4px; background: #fff; border: 1px solid #e2e8f0;
        border-radius: 10px; box-shadow: 0 12px 24px rgba(15, 23, 42, .12);
    }
    .dd.is-open .dd-menu { display: block; }

    .dd-item {
        width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 16px;
        padding: 8px 10px; font-family: inherit; font-size: 11px; color: #334155; text-align: left;
        background: none; border: 0; border-radius: 7px; cursor: pointer; white-space: nowrap;
    }
    .dd-item:hover, .dd-item:focus-visible { background: #f8fafc; outline: none; }
    .dd-item.is-selected { background: #e0f2fe; color: #0369a1; font-weight: 600; }
    .dd-item.is-selected::after { content: "✓"; font-size: 10px; }
</style>

<script>
(function () {
    function tutupSemua(kecuali) {
        document.querySelectorAll('[data-dd].is-open').forEach(function (d) {
            if (d === kecuali) return;
            d.classList.remove('is-open');
            d.querySelector('[data-dd-trigger]').setAttribute('aria-expanded', 'false');
        });
    }

    document.addEventListener('click', function (e) {
        var trig = e.target.closest('[data-dd-trigger]');
        if (trig) {
            var dd = trig.closest('[data-dd]');
            var buka = !dd.classList.contains('is-open');
            tutupSemua(dd);
            dd.classList.toggle('is-open', buka);
            trig.setAttribute('aria-expanded', buka ? 'true' : 'false');
            return;
        }

        var item = e.target.closest('[data-dd] .dd-item');
        if (item) {
            var wrap = item.closest('[data-dd]');
            wrap.querySelector('[data-dd-input]').value = item.dataset.value;
            wrap.querySelector('.dd-text').textContent = item.dataset.label;
            wrap.querySelectorAll('.dd-item').forEach(function (b) {
                var on = b === item;
                b.classList.toggle('is-selected', on);
                b.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            tutupSemua(null);
            if (wrap.dataset.autosubmit === '1') {
                wrap.closest('form').submit();
            }
            return;
        }

        tutupSemua(null);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') tutupSemua(null);
    });
})();
</script>
@endonce

<div class="dd" data-dd @if ($autosubmit) data-autosubmit="1" @endif>
    <input type="hidden" name="{{ $name }}" value="{{ $nilai }}" data-dd-input>

    <button type="button" class="dd-trigger" aria-haspopup="listbox" aria-expanded="false" data-dd-trigger>
        <span class="dd-text">{{ $terpilih }}</span>
        <svg class="dd-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m6 9 6 6 6-6"/>
        </svg>
    </button>

    <div class="dd-menu" role="listbox">
        @foreach ($options as $val => $lbl)
            <button type="button" role="option"
                    class="dd-item {{ (string) $val === $nilai ? 'is-selected' : '' }}"
                    data-value="{{ $val }}"
                    data-label="{{ $lbl }}"
                    aria-selected="{{ (string) $val === $nilai ? 'true' : 'false' }}">{{ $lbl }}</button>
        @endforeach
    </div>
</div>