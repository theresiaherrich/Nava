<x-layouts.admin title="Edit Dimensi - Admin SIAKAD">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Edit Dimensi</h1>
                <p class="text-xs text-gray-500 mt-1">Perbarui deskripsi dimensi RIASEC.</p>
            </div>
            <a href="{{ route('admin.dimensi.index') }}"
               class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition w-fit">
                &larr; Kembali
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm max-w-2xl">

        {{-- Info Readonly --}}
        <div class="grid grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
            <div>
                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Kode</p>
                <p class="text-sm font-extrabold text-[#009bf2] tracking-widest">{{ $dimensi->kode }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Nama</p>
                <p class="text-sm font-bold text-[#0f1b33]">{{ $dimensi->nama }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.dimensi.update', $dimensi->id_dimensi) }}">
            @csrf
            @method('PUT')

            {{-- Deskripsi --}}
            <div class="mb-5">
                <label for="deskripsi" class="block text-xs font-semibold text-gray-700 mb-1">
                    Deskripsi <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    rows="5"
                    minlength="10"
                    required
                    placeholder="Masukkan deskripsi dimensi (minimal 10 karakter)..."
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('deskripsi') border-red-400 @enderror"
                >{{ old('deskripsi', $dimensi->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-4 py-2 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition">
                    Simpan Perubahan
                </button>
                <button type="button"
                        onclick="history.back()"
                        class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition">
                    Batal
                </button>
            </div>
        </form>
    </div>

</x-layouts.admin>
