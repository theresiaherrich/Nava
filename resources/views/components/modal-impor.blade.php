@php $aktif = old('_modal') === $id && $errors->any(); @endphp

<div class="mk-overlay" id="{{ $id }}" @if ($aktif) data-auto-open @endif>
    <div class="mk-box" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-judul">
        <div class="mk-head">
            <div>
                <h3 id="{{ $id }}-judul" class="mk-title">{{ $judul }}</h3>
                <p class="mk-desc">Unggah berkas Excel sesuai format, periksa, lalu simpan.</p>
            </div>
            <button type="button" class="mk-close" data-mk-close aria-label="Tutup">×</button>
        </div>

        <div class="mk-steps">
            <div class="mk-step is-active"><span class="mk-num">1</span> Unggah berkas</div>
            <span class="mk-line"></span>
            <div class="mk-step"><span class="mk-num">2</span> Periksa data</div>
            <span class="mk-line"></span>
            <div class="mk-step"><span class="mk-num">3</span> Simpan</div>
        </div>

        <form method="POST" action="{{ $aksi }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_modal" value="{{ $id }}">
            <input type="file" name="berkas" accept=".xlsx,.xls,.csv" hidden data-file-input>

            <div class="mk-drop" tabindex="0" role="button" aria-label="Pilih berkas Excel" data-dropzone>
                <div class="mk-drop-icon">⤒</div>
                <p class="mk-drop-title">Drop file di sini</p>
                <p class="mk-drop-text">atau <span class="mk-drop-link">pilih dari komputer</span></p>
                <p class="mk-drop-text">Format .xlsx, .xls, atau .csv · maksimal 5 MB</p>
                <p class="mk-error" data-drop-msg hidden></p>
            </div>

            <div class="mk-file" data-file-info hidden>
                <div class="mk-file-icon">XLS</div>
                <div class="mk-file-body">
                    <p class="mk-file-name" data-file-name></p>
                    <p class="mk-desc" data-file-size></p>
                </div>
                <button type="button" class="mk-btn mk-btn-sm" data-ganti-berkas>Ganti</button>
                <button type="button" class="mk-btn mk-btn-sm" data-hapus-berkas>Hapus</button>
            </div>

            @error('berkas')
                <p class="mk-error">{{ $message }}</p>
            @enderror

            <div class="mk-foot">
                <a href="{{ $formatUrl }}" class="mk-link mk-foot-left">Unduh format</a>
                <button type="button" class="mk-btn" data-mk-close>Batal</button>
                <button type="submit" class="mk-btn-primary" data-submit-impor disabled>Unggah &amp; Simpan</button>
            </div>
        </form>
    </div>
</div>