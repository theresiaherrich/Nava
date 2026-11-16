<x-layouts.admin title="Tambah Mapel Pilihan - Admin SIAKAD">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-[#0f1b33]">Tambah Mata Pelajaran Pilihan</h1>
                <p class="text-xs text-gray-500 mt-1">Tambahkan data mata pelajaran pilihan baru beserta profil RIASEC.</p>
            </div>
            <a href="{{ route('admin.mapel') }}"
                class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition">
                Kembali
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-2xl">
        <form action="{{ route('admin.mapel.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kode Mata Pelajaran</label>
                <input type="text" name="kode" value="{{ old('kode') }}" required placeholder="Contoh: KODE-FIS"
                    class="w-full px-3.5 py-2.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#009bf2] focus:ring-1 focus:ring-[#009bf2] outline-hidden uppercase font-mono">
                @error('kode') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Mata Pelajaran</label>
                <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: Fisika Lanjut"
                    class="w-full px-3.5 py-2.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#009bf2] focus:ring-1 focus:ring-[#009bf2] outline-hidden">
                @error('nama') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Rumpun</label>
                <input type="text" name="rumpun" value="{{ old('rumpun') }}" required placeholder="Contoh: Sains & Teknologi"
                    class="w-full px-3.5 py-2.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#009bf2] focus:ring-1 focus:ring-[#009bf2] outline-hidden">
                @error('rumpun') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kode Profil RIASEC (3 Huruf Unik)</label>
                <input type="text" name="kode_riasec" value="{{ old('kode_riasec') }}" required maxlength="3" placeholder="Contoh: IRS"
                    class="w-full px-3.5 py-2.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#009bf2] focus:ring-1 focus:ring-[#009bf2] outline-hidden uppercase font-mono tracking-widest">
                <p class="text-[10px] text-gray-400 mt-1">Gunakan kombinasi 3 huruf berbeda dari R, I, A, S, E, C tanpa tanda hubung.</p>
                @error('kode_riasec') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Singkat</label>
                <textarea name="deskripsi" rows="3" placeholder="Deskripsi mata pelajaran dan orientasi pembelajarannya..."
                    class="w-full px-3.5 py-2.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#009bf2] focus:ring-1 focus:ring-[#009bf2] outline-hidden">{{ old('deskripsi') }}</textarea>
                @error('deskripsi') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100">
                <a href="{{ route('admin.mapel') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition shadow-sm">
                    Simpan Mapel
                </button>
            </div>
        </form>
    </div>

</x-layouts.admin>
