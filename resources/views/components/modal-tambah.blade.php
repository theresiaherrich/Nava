@php $aktif = old('_modal') === $id && $errors->any(); @endphp

<div class="mk-overlay" id="{{ $id }}" @if ($aktif) data-auto-open @endif>
    <div class="mk-box" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-judul">
        <div class="mk-head">
            <div>
                <h3 id="{{ $id }}-judul" class="mk-title">{{ $judul }}</h3>
                <p class="mk-desc">Isi sesuai dokumen resmi. Data ini dipakai di seluruh modul SIAKAD.</p>
            </div>
            <button type="button" class="mk-close" data-mk-close aria-label="Tutup">×</button>
        </div>

        <form method="POST" action="{{ $aksi }}">
            @csrf
            <input type="hidden" name="_modal" value="{{ $id }}">

            <div class="mk-grid">
                @foreach ($fields as $f)
                    @php
                        $nama    = $f['name'];
                        $idField = $id . '-' . $nama;
                    @endphp

                    <div class="mk-field {{ ! empty($f['full']) ? 'mk-full' : '' }}">
                        <label for="{{ $idField }}">
                            {{ $f['label'] }}
                            @if (! empty($f['required']))<span style="color:#dc2626">*</span>@endif
                        </label>

                        @if (($f['type'] ?? 'text') === 'select')
                            <select id="{{ $idField }}" name="{{ $nama }}"
                                    class="mk-select {{ $errors->has($nama) ? 'is-error' : '' }}">
                                @foreach ($f['options'] as $val => $lbl)
                                    <option value="{{ $val }}" @selected(old($nama) == $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="{{ $idField }}" type="text" name="{{ $nama }}"
                                   value="{{ old($nama) }}"
                                   placeholder="{{ $f['placeholder'] ?? '' }}"
                                   class="mk-input {{ $errors->has($nama) ? 'is-error' : '' }}"
                                   @if (! empty($f['required'])) required @endif>
                        @endif

                        @error($nama)
                            <p class="mk-error">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mk-foot">
                <button type="button" class="mk-btn" data-mk-close>Batal</button>
                <button type="submit" class="mk-btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>