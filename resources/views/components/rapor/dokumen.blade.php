@props([
    'doc' => 'doc',     // ekspresi Alpine berisi dokumen rapor (bentuk App\Support\Dummy\Rapor::dokumen)
    'draf' => 'false',  // ekspresi Alpine: tampilkan watermark DRAFT (nilai belum dikunci)
])


{{--
    Dokumen rapor (kertas A4, 540px) — Rapor STS 1 halaman, Rapor Semester 2 halaman.
    Dipakai Format Rapor (pratinjau langsung), Cetak Rapor, Arsip, dan Portal Siswa.
--}}
<div {{ $attributes->class('flex flex-col items-center gap-5 text-ink') }} x-data="{ get d() { return {{ $doc }} } }">
    {{-- Halaman 1 --}}
    <article class="relative flex min-h-[764px] w-[540px] flex-col bg-white px-9 pt-8 pb-6 text-[8px] leading-snug shadow-card" aria-label="Rapor halaman 1" data-halaman="1">
        @include('components.rapor._draf')
        @include('components.rapor._kop')

        <h3 class="mt-4 text-center text-[10px] font-bold uppercase" x-text="d.jenis === 'sts' ? 'Laporan Hasil Belajar — Sumatif Tengah Semester' : `Laporan Hasil Belajar — Semester ${d.semester}`"></h3>
        <p class="text-center text-[8.5px]" x-text="`Tahun Ajaran ${d.tahun_ajaran}`"></p>

        <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-0.5 text-[8px]">
            <div class="flex"><dt class="w-[88px] shrink-0">Nama Peserta Didik</dt><dd class="font-bold">: <span x-text="d.siswa.nama"></span></dd></div>
            <div class="flex"><dt class="w-[52px] shrink-0">Kelas</dt><dd>: <span x-text="d.siswa.kelas"></span></dd></div>
            <div class="flex"><dt class="w-[88px] shrink-0" x-text="d.jenis === 'sts' ? 'NISN' : 'NIS / NISN'"></dt><dd>: <span x-text="d.jenis === 'sts' ? d.siswa.nisn : `${d.siswa.nis} / ${d.siswa.nisn}`"></span></dd></div>
            <div class="flex"><dt class="w-[52px] shrink-0">Fase</dt><dd>: <span x-text="d.siswa.fase"></span></dd></div>
            <template x-if="d.jenis === 'semester'">
                <div class="flex"><dt class="w-[88px] shrink-0">Sekolah</dt><dd>: <span x-text="d.siswa.sekolah"></span></dd></div>
            </template>
            <template x-if="d.jenis === 'semester'">
                <div class="flex"><dt class="w-[52px] shrink-0">Semester</dt><dd>: <span x-text="d.semester"></span></dd></div>
            </template>
        </dl>

        {{-- Tabel STS: nilai per TP --}}
        <table x-show="d.jenis === 'sts'" class="mt-4 w-full border-collapse text-[7.5px]">
            <thead class="bg-neutral-soft">
                <tr>
                    <th class="w-6 border border-ink px-1 py-1">No</th>
                    <th class="border border-ink px-1.5 py-1 text-left">Mata pelajaran</th>
                    <template x-for="(t, i) in (d.mapel[0]?.tp ?? [])" :key="i"><th class="w-9 border border-ink px-1 py-1" x-text="`TP${i + 1}`"></th></template>
                    <th class="w-12 border border-ink px-1 py-1">Nilai akhir</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(m, i) in d.mapel" :key="m.nama">
                    <tr>
                        <td class="border border-ink px-1 py-1 text-center" x-text="i + 1"></td>
                        <td class="border border-ink px-1.5 py-1" x-text="m.nama"></td>
                        <template x-for="(n, j) in m.tp" :key="j"><td class="border border-ink px-1 py-1 text-center" x-text="n"></td></template>
                        <td class="border border-ink px-1 py-1 text-center font-bold" x-text="m.akhir"></td>
                    </tr>
                </template>
            </tbody>
        </table>

        {{-- Tabel semester: nilai akhir + capaian kompetensi --}}
        <table x-show="d.jenis === 'semester'" class="mt-4 w-full border-collapse text-[7px]">
            <thead class="bg-neutral-soft">
                <tr>
                    <th class="w-6 border border-ink px-1 py-1">No</th>
                    <th class="w-[110px] border border-ink px-1.5 py-1 text-left">Mata Pelajaran</th>
                    <th class="w-11 border border-ink px-1 py-1">Nilai Akhir</th>
                    <th x-show="d.komponen.capaian" class="border border-ink px-1.5 py-1 text-left">Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(m, i) in d.mapel" :key="m.nama">
                    <tr class="align-top">
                        <td class="border border-ink px-1 py-1 text-center" x-text="i + 1"></td>
                        <td class="border border-ink px-1.5 py-1" x-text="m.nama"></td>
                        <td class="border border-ink px-1 py-1 text-center" x-text="m.akhir"></td>
                        <td x-show="d.komponen.capaian" class="border border-ink px-1.5 py-1" x-text="m.capaian"></td>
                    </tr>
                </template>
            </tbody>
        </table>

        {{-- Tanda tangan (STS di halaman 1) --}}
        <template x-if="d.jenis === 'sts'">
            <div class="mt-9 grid grid-cols-2 text-center text-[8px]">
                <div><p>Mengetahui,</p><p>Orang Tua/Wali</p><p class="mx-auto mt-12 w-36 border-b border-ink"></p></div>
                <div>
                    <p x-text="`${d.ttd.tempat}, ${$tanggal(d.ttd.tanggal)}`"></p>
                    <p x-text="`Wali Kelas ${d.siswa.kelas}`"></p>
                    <p class="mt-12 font-bold" x-text="d.ttd.wali"></p>
                    <p>NIP [NIP Wali Kelas]</p>
                </div>
            </div>
        </template>

        <p class="mt-auto pt-6 text-right text-[7px] text-muted" x-text="`Halaman 1 dari ${d.jenis === 'sts' ? 1 : 2}`"></p>
    </article>

    {{-- Halaman 2 (rapor semester) --}}
    <template x-if="d.jenis === 'semester'">
        <article class="relative flex min-h-[764px] w-[540px] flex-col bg-white px-9 pt-8 pb-6 text-[8px] leading-snug shadow-card" aria-label="Rapor halaman 2" data-halaman="2">
            @include('components.rapor._draf')
            <template x-if="d.komponen.kokurikuler">
                <section><h4 class="text-[8.5px] font-bold">A. Kokurikuler</h4><p class="mt-1 border border-ink px-2 py-1.5" x-text="d.kokurikuler"></p></section>
            </template>
            <template x-if="d.komponen.ekskul">
                <section class="mt-4">
                    <h4 class="text-[8.5px] font-bold">B. Ekstrakurikuler</h4>
                    <table class="mt-1 w-full border-collapse text-[7.5px]">
                        <thead class="bg-neutral-soft"><tr><th class="w-6 border border-ink px-1 py-1">No</th><th class="border border-ink px-1.5 py-1 text-left">Kegiatan</th><th class="w-14 border border-ink px-1 py-1">Predikat</th><th class="border border-ink px-1.5 py-1 text-left">Keterangan</th></tr></thead>
                        <tbody>
                            <template x-for="(e, i) in d.ekskul" :key="e.nama">
                                <tr><td class="border border-ink px-1 py-1 text-center" x-text="i + 1"></td><td class="border border-ink px-1.5 py-1" x-text="e.nama"></td><td class="border border-ink px-1 py-1 text-center" x-text="e.predikat"></td><td class="border border-ink px-1.5 py-1" x-text="e.ket"></td></tr>
                            </template>
                        </tbody>
                    </table>
                </section>
            </template>
            <template x-if="d.komponen.absen">
                <section class="mt-4">
                    <h4 class="text-[8.5px] font-bold">C. Ketidakhadiran</h4>
                    <table class="mt-1 w-1/2 border-collapse text-[7.5px]">
                        <tbody>
                            <tr><td class="border border-ink px-1.5 py-1">Sakit</td><td class="w-16 border border-ink px-1.5 py-1 text-center" x-text="`${d.absen.sakit} hari`"></td></tr>
                            <tr><td class="border border-ink px-1.5 py-1">Izin</td><td class="border border-ink px-1.5 py-1 text-center" x-text="`${d.absen.izin} hari`"></td></tr>
                            <tr><td class="border border-ink px-1.5 py-1">Tanpa keterangan</td><td class="border border-ink px-1.5 py-1 text-center" x-text="`${d.absen.alpa} hari`"></td></tr>
                        </tbody>
                    </table>
                </section>
            </template>
            <template x-if="d.komponen.catatan">
                <section class="mt-4"><h4 class="text-[8.5px] font-bold">D. Catatan Wali Kelas</h4><p class="mt-1 border border-ink px-2 py-1.5" x-text="d.catatan"></p></section>
            </template>

            <div class="mt-8 grid grid-cols-2 text-center text-[8px]">
                <div><p>Mengetahui,</p><p>Orang Tua/Wali</p><p class="mx-auto mt-12 w-36 border-b border-ink"></p></div>
                <div>
                    <p x-text="`${d.ttd.tempat}, ${$tanggal(d.ttd.tanggal)}`"></p>
                    <p x-text="`Wali Kelas ${d.siswa.kelas}`"></p>
                    <p class="mt-12 font-bold" x-text="d.ttd.wali"></p>
                </div>
            </div>
            <div class="mt-6 text-center text-[8px]">
                <p>Mengetahui,</p><p>Kepala Sekolah</p>
                <p class="mt-12 font-bold" x-text="d.ttd.kepsek"></p>
                <p x-text="`NIP ${d.ttd.nip_kepsek}`"></p>
            </div>
            <div x-show="d.komponen.qr" class="absolute bottom-10 left-9 flex items-center gap-2 text-[7px] text-muted">
                <span class="grid size-12 grid-cols-4 gap-px border border-ink p-0.5" aria-hidden="true">
                    @foreach (str_split('1011010111001101') as $bit)<span @class(['bg-ink' => $bit === '1'])></span>@endforeach
                </span>
                Pindai untuk verifikasi keaslian rapor
            </div>
            <p class="mt-auto pt-6 text-right text-[7px] text-muted">Halaman 2 dari 2</p>
        </article>
    </template>
</div>
