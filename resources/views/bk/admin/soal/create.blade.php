<x-layouts.admin title="Tambah Soal - Admin SIAKAD">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Tambah Soal</h1>
                <p class="text-xs text-gray-500 mt-1">Tambahkan soal baru ke bank soal RIASEC.</p>
            </div>
            <a href="{{ route('admin.soal.index') }}"
               class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition w-fit">
                &larr; Kembali
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm max-w-2xl">
        <form method="POST" action="{{ route('admin.soal.store') }}">
            @csrf

            {{-- Dimensi --}}
            <div class="mb-5">
                <label for="id_dimensi" class="block text-xs font-semibold text-gray-700 mb-1">
                    Dimensi <span class="text-red-500">*</span>
                </label>
                <select
                    id="id_dimensi"
                    name="id_dimensi"
                    required
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('id_dimensi') border-red-400 @enderror"
                >
                    <option value="">-- Pilih Dimensi --</option>
                    @foreach($dimensiList as $dimensi)
                        <option value="{{ $dimensi->id_dimensi }}" {{ old('id_dimensi') == $dimensi->id_dimensi ? 'selected' : '' }}>
                            [{{ $dimensi->kode }}] {{ $dimensi->nama }}
                        </option>
                    @endforeach
                </select>
                @error('id_dimensi')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Teks Soal --}}
            <div class="mb-5">
                <label for="teks" class="block text-xs font-semibold text-gray-700 mb-1">
                    Teks Soal <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="teks"
                    name="teks"
                    rows="4"
                    minlength="10"
                    required
                    placeholder="Tuliskan teks soal (minimal 10 karakter)..."
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('teks') border-red-400 @enderror"
                >{{ old('teks') }}</textarea>
                @error('teks')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status Aktif --}}
            <div class="mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        name="aktif"
                        value="1"
                        {{ old('aktif', '1') == '1' ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 text-[#009bf2] focus:ring-[#009bf2]/30"
                    >
                    <span class="text-xs font-semibold text-gray-700">Aktifkan soal ini</span>
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-4 py-2 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition">
                    Simpan Soal
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
