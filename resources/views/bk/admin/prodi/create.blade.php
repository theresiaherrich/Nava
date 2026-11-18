<x-layouts.admin title="Tambah Program Studi - Admin SIAKAD">

    <div class="max-w-6xl mx-auto py-3">
        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <x-page-label />
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mt-1">
                <div>
                    <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">Tambah Program Studi</h1>
                    <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">Tambahkan data program studi baru, profil RIASEC, dan prospek profesi.</p>
                </div>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-2xl">
            <form action="{{ route('admin.prodi.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Program Studi</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: Teknik Informatika"
                        class="w-full px-3.5 py-2.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#089BD5] focus:ring-2 focus:ring-[#089BD5]/20 outline-hidden">
                    @error('nama') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Rumpun Ilmu</label>
                    <input type="text" name="rumpun" value="{{ old('rumpun') }}" placeholder="Contoh: Sains dan Teknologi"
                        class="w-full px-3.5 py-2.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#089BD5] focus:ring-2 focus:ring-[#089BD5]/20 outline-hidden">
                    @error('rumpun') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Profil RIASEC (3 Huruf Unik)</label>
                    <input type="text" name="kode_riasec" value="{{ old('kode_riasec') }}" required maxlength="3" placeholder="Contoh: IRC"
                        class="w-full px-3.5 py-2.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#089BD5] focus:ring-2 focus:ring-[#089BD5]/20 outline-hidden uppercase font-mono tracking-widest">
                    <p class="text-[10px] text-gray-400 mt-1">Gunakan kombinasi 3 huruf berbeda dari R, I, A, S, E, C tanpa tanda hubung.</p>
                    @error('kode_riasec') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tentang Program Studi</label>
                    <textarea name="tentang" rows="2" placeholder="Fokus kajian utama..."
                        class="w-full px-3.5 py-2.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#089BD5] focus:ring-2 focus:ring-[#089BD5]/20 outline-hidden">{{ old('tentang') }}</textarea>
                    @error('tentang') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Lengkap</label>
                    <textarea name="deskripsi" rows="3" placeholder="Deskripsi prospek dan kompetensi..."
                        class="w-full px-3.5 py-2.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#089BD5] focus:ring-2 focus:ring-[#089BD5]/20 outline-hidden">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Prospek Profesi Terkait</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 bg-gray-50 border border-gray-200 rounded-xl max-h-48 overflow-y-auto">
                        @forelse($profesiList as $pf)
                            <label class="flex items-center gap-2 text-xs text-slate-700 hover:text-[#089BD5] cursor-pointer">
                                <input type="checkbox" name="profesi[]" value="{{ $pf->id_profesi }}"
                                    {{ in_array($pf->id_profesi, old('profesi', [])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-[#089BD5] focus:ring-[#089BD5]">
                                <span class="truncate">{{ $pf->nama }}</span>
                            </label>
                        @empty
                            <p class="text-xs text-gray-400 col-span-3">Belum ada master profesi aktif.</p>
                        @endforelse
                    </div>
                    @error('profesi') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100">
                    <a href="{{ route('admin.prodi') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2 bg-[#089BD5] text-white text-xs font-bold rounded-xl hover:bg-[#078ec4] transition shadow-sm">
                        Simpan Program Studi
                    </button>
                </div>
            </form>
        </div>
    </div>{{-- /max-w-6xl --}}

</x-layouts.admin>
