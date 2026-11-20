<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** A4: seluruh sumber data portal diambil dari database SIAKAD. */
abstract class PortalBaseController extends Controller
{
    protected const KKTP = 75;
    protected const NILAI_OPTIMAL = 85;


    protected function siswaId(): ?int
    {
        $ortuId = session('ortu_id_user');

        if ($ortuId) {
            $query = DB::table('orang_tua')
                ->where('id_user', $ortuId)
                ->where('status_verifikasi', 'verified');

            $idSiswa = session('portal_siswa_id');

            if ($idSiswa) {
                $query->where('id_siswa', $idSiswa);
            }

            $result = $query->value('id_siswa');

            return $result ? (int) $result : null;
        }

        if (app()->environment(['local', 'testing'])) {
            $id = session('portal_siswa_id') ?: env('PORTAL_SISWA_ID');

            return $id ? (int) $id : null;
        }

        return null;
    }


    protected function siswaRow(): ?object
    {
        $id = $this->siswaId();
        return $id ? DB::table('siswa')->where('id_siswa', $id)->first() : null;
    }

    protected function riwayatAktif(): ?object
    {
        $id = $this->siswaId();
        if (!$id) return null;
        return DB::table('riwayat_kelas as rk')
            ->join('kelas as k', 'k.id_kelas', '=', 'rk.id_kelas')
            ->join('semester as sm', 'sm.id_semester', '=', 'rk.id_semester')
            ->join('tahun_ajaran as ta', 'ta.id_tahun_ajaran', '=', 'sm.id_tahun_ajaran')
            ->where('rk.id_siswa', $id)
            ->orderByDesc('sm.aktif')->orderByDesc('ta.aktif')->orderByDesc('rk.id_riwayat_kelas')
            ->select('rk.*', 'k.nama as kelas_nama', 'k.tingkat', 'k.fase', 'sm.nama as semester_nama', 'sm.aktif as semester_aktif', 'ta.nama as tahun_ajaran')
            ->first();
    }

    protected function inisial(?string $nama): string
    {
        return collect(preg_split('/\s+/', trim((string) $nama)))->filter()->take(2)->map(fn ($x) => mb_substr($x, 0, 1))->implode('') ?: '—';
    }

    protected function sapaan(): string
    {
        $jam = (int) now()->format('G');
        return $jam < 11 ? 'Selamat Pagi' : ($jam < 15 ? 'Selamat Siang' : ($jam < 18 ? 'Selamat Sore' : 'Selamat Malam'));
    }

    protected function predikat(float $n): string
    {
        return match (true) { $n >= 90 => 'A', $n >= 85 => 'B+', $n >= 80 => 'B', $n >= self::KKTP => 'C', default => 'D' };
    }

    protected function ketercapaian(float $n): array
    {
        return match (true) {
            $n >= 90 => ['label' => 'Sangat Baik', 'kelas' => 'sangat-baik'],
            $n >= 80 => ['label' => 'Baik', 'kelas' => 'baik'],
            $n >= self::KKTP => ['label' => 'Cukup', 'kelas' => 'cukup'],
            default => ['label' => 'Perlu Penguatan', 'kelas' => 'perlu'],
        };
    }

    protected function rataRata($nilai): float
    {
        $terisi = $nilai->filter(fn ($row) => $row->nilai !== null);
        return $terisi->isEmpty() ? 0.0 : round((float) $terisi->avg('nilai'), 2);
    }

    protected function labelStatus(): array
    {
        return ['hadir'=>'Hadir Tepat Waktu','toleransi'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpha','libur'=>'Libur','belum'=>'Belum ada catatan'];
    }

    protected function dataSiswa(): object
    {
        $s = $this->siswaRow();
        $rk = $this->riwayatAktif();
        $wali = null;
        if ($rk) {
            $wali = DB::table('wali_kelas as wk')->join('guru as g', 'g.id_guru', '=', 'wk.id_guru')
                ->where('wk.id_kelas', $rk->id_kelas)->where('wk.id_semester', $rk->id_semester)
                ->select('g.nama', 'g.nip')->first();
        }
        $nama = $s->nama ?? 'Data siswa belum tersedia';
        return (object) [
            'tersedia' => (bool) $s, 'id_siswa' => $s->id_siswa ?? null, 'nama' => $nama,
            'nama_pendek' => $nama, 'inisial' => $this->inisial($nama), 'nis' => $s->nis ?? null, 'nisn' => $s->nisn ?? null,
            'no_presensi' => $s->urutan_masuk ?? null, 'kelas' => $rk->kelas_nama ?? '—', 'kelas_singkat' => $rk->kelas_nama ?? '—',
            'peminatan' => null, 'fase' => isset($rk->fase) ? 'Fase '.$rk->fase : '—', 'tahun_ajaran' => $rk->tahun_ajaran ?? null,
            'semester' => isset($rk->semester_nama) ? ucfirst(strtolower($rk->semester_nama)) : '—', 'angkatan' => $s->tahun_masuk ?? null,
            'jalur' => null, 'status' => '—', 'foto' => null, 'tempat_lahir' => null, 'tanggal_lahir' => null, 'umur' => null,
            'jenis_kelamin' => ($s->jenis_kelamin ?? null) === 'L' ? 'Laki-laki' : ((($s->jenis_kelamin ?? null) === 'P') ? 'Perempuan' : '—'),
            'agama' => $s->agama ?? null, 'alamat' => $s->alamat ?? null, 'jarak_sekolah' => null, 'kurikulum' => null, 'ruang' => null,
            'rombel_label' => $rk->kelas_nama ?? '—',
            'wali_kelas' => (object) ['nama' => $wali->nama ?? '—', 'nip' => $wali->nip ?? '—', 'inisial' => $this->inisial($wali->nama ?? ''), 'tersedia' => (bool) $wali],
        ];
    }


protected function dataOrtu(): object
{
    $ortuId = session('ortu_id_user');
    $idSiswa = $this->siswaId();

    $o = null;

    if ($ortuId) {
        $query = DB::table('orang_tua')
            ->where('id_user', $ortuId)
            ->where('status_verifikasi', 'verified');

        if ($idSiswa) {
            $query->where('id_siswa', $idSiswa);
        }

        $o = $query->first();
    }

    // Fallback hanya untuk lingkungan lokal/testing.
    // Tidak digunakan untuk memilih akun pada production.
    elseif ($idSiswa && app()->environment(['local', 'testing'])) {
        $o = DB::table('orang_tua')
            ->where('id_siswa', $idSiswa)
            ->where('status_verifikasi', 'verified')
            ->orderByRaw("FIELD(hubungan,'Ibu','Ayah','Wali')")
            ->first();
    }

    // Selalu kembalikan object agar tidak menyebabkan TypeError.
    return (object) [
        'id_user' => $o->id_user ?? null,
        'id_siswa' => $o->id_siswa ?? $idSiswa,
        'nama' => $o->nama ?? 'Orang Tua',
        'hubungan' => $o->hubungan ?? 'Orang Tua',
        'no_telepon' => $o->no_telepon ?? null,
        'alamat' => $o->alamat ?? null,
        'jenis_kelamin' => $o->jenis_kelamin ?? null,
        'foto' => null,
        'inisial' => $this->inisial($o->nama ?? 'Orang Tua'),
        'peran' => $o->hubungan ?? 'Orang Tua',
        'tersedia' => (bool) $o,
    ];
}


    protected function dataWaliMurid(): array
    {
        if (!$this->siswaId()) return [];
        return DB::table('orang_tua')->where('id_siswa', $this->siswaId())->orderByRaw("FIELD(hubungan,'Ibu','Ayah','Wali')")->get()->map(fn ($o) => (object) [
            'nama'=>$o->nama, 'hubungan'=>$o->hubungan, 'inisial'=>$this->inisial($o->nama),
            'keterangan'=>match($o->status_verifikasi){'verified'=>'Terverifikasi','rejected'=>'Ditolak',default=>'Menunggu verifikasi'},
            'utama'=>false, 'wa'=>$o->no_telepon,
        ])->all();
    }

    protected function dataKegiatan(): array
    {
        if (!$this->siswaId()) return [];
        return DB::table('peserta_ekskul as pe')->join('ekskul as e','e.id_ekskul','=','pe.id_ekskul')
            ->leftJoin('predikat as p','p.id_predikat','=','pe.id_predikat')->where('pe.id_siswa',$this->siswaId())
            ->select('e.nama as judul','p.predikat','p.keterangan','pe.catatan')->get()->map(fn($r)=>(object)[
                'judul'=>$r->judul,'ikon'=>'compass','badge'=>$r->predikat ? 'Predikat: '.$r->predikat : 'Terdaftar',
                'tipe'=>'biru','detail'=>$r->catatan ?: ($r->keterangan ?: '—')
            ])->all();
    }

    protected function dataKehadiranHariIni(): object
    {
        $r = $this->siswaId() ? DB::table('presensi')->where('id_siswa',$this->siswaId())->whereDate('tanggal',today())->first() : null;
        $label = !$r ? 'Belum ada catatan' : ($r->status === 'Terlambat' ? 'Terlambat' : $r->status);
        return (object)['jam'=>$r?->waktu_presensi ? Carbon::parse($r->waktu_presensi)->format('H.i') : '—','gerbang'=>'—','label'=>$label];
    }

    protected function dataPresensiSemester(): object
    {
        $rows = $this->siswaId() ? DB::table('presensi')->where('id_siswa',$this->siswaId())->get() : collect();
        $total=$rows->count(); $hadir=$rows->where('status','Hadir')->count(); $telat=$rows->where('status','Terlambat')->count();
        $sakit=$rows->where('status','Sakit')->count(); $izin=$rows->where('status','Izin')->count(); $alpha=$rows->where('status','Alpa')->count();
        $efektif=$hadir+$telat+$sakit+$izin+$alpha;
        return (object)['efektif'=>$efektif,'hadir'=>$hadir+$telat,'sakit'=>$sakit,'izin'=>$izin,'alpha'=>$alpha,'tepat_waktu'=>$hadir,'toleransi'=>$telat,
            'persen_hadir'=>$efektif ? round(($hadir+$telat)/$efektif*100,1):0,'persen_sakit'=>$efektif?round($sakit/$efektif*100,2):0,
            'persen_izin'=>$efektif?round($izin/$efektif*100,2):0,'persen_alpha'=>$efektif?round($alpha/$efektif*100,2):0,'tren'=>null,'ket_sakit'=>null,'ket_izin'=>null];
    }

    protected function dataNilai(): Collection
    {
        $rk=$this->riwayatAktif(); if(!$rk) return collect();
        $capaian = DB::table('siswa_rombel as sr')->join('nilai_tp as nt','nt.id_siswarombel','=','sr.id_siswarombel')
            ->join('tp as t','t.id_tp','=','nt.id_tp')->where('sr.id_riwayat_kelas',$rk->id_riwayat_kelas)
            ->select('sr.id_rombel',DB::raw("GROUP_CONCAT(DISTINCT t.deskripsi SEPARATOR ' · ') as capaian"))
            ->groupBy('sr.id_rombel');
        return DB::table('nilai_semester as ns')->join('rombel as r','r.id_rombel','=','ns.id_rombel')
            ->join('mapel as m','m.id_mapel','=','r.id_mapel')
            ->leftJoin('guru_rombel as gr','gr.id_rombel','=','r.id_rombel')->leftJoin('guru as g','g.id_guru','=','gr.id_guru')
            ->leftJoinSub($capaian,'tp_siswa',fn($j)=>$j->on('tp_siswa.id_rombel','=','r.id_rombel'))
            ->where('ns.id_riwayat_kelas',$rk->id_riwayat_kelas)
            ->select('m.nama as mapel','r.kelompok_rapor as kelompok','ns.nilai_akhir as nilai','g.nama as guru','tp_siswa.capaian')
            ->orderBy('m.nama')->get()->map(function($r,$i){$n=$r->nilai !== null ? (float)$r->nilai : null;return(object)[
                'no'=>$i+1,'mapel'=>$r->mapel,'kelompok'=>$r->kelompok ?: '—','kelompok_kelas'=>strtolower($r->kelompok ?: 'umum'),
                'nilai'=>$n,'guru'=>$r->guru ?: '—','capaian'=>$r->capaian ?: '', 'predikat'=>$n === null ? '—' : $this->predikat($n), 'tp'=>$n === null ? ['label'=>'—','kelas'=>'kosong'] : $this->ketercapaian($n),'kktp'=>self::KKTP];});
    }

    protected function dataCatatanSekolah(): array
    {
        $rk=$this->riwayatAktif(); if(!$rk) return [];
        $wali=DB::table('wali_kelas as wk')->join('guru as g','g.id_guru','=','wk.id_guru')
            ->where('wk.id_kelas',$rk->id_kelas)->where('wk.id_semester',$rk->id_semester)->select('g.nama','g.nip')->first();
        return DB::table('pelengkap_rapor')->where('id_riwayat_kelas',$rk->id_riwayat_kelas)
            ->whereNotNull('catatan_wali')->where('catatan_wali','!=','')->get()
            ->map(fn($r)=>(object)['nama'=>$wali->nama ?? 'Wali Kelas','peran'=>'Catatan Wali Kelas','nip'=>$wali->nip ?? '—',
                'tanggal'=>null,'isi'=>$r->catatan_wali,'inisial'=>$this->inisial($wali->nama ?? ''),'aksi'=>'Informasi','aksi_ikon'=>'info'])->all();
    }

    protected function dataRiasec(): ?object
    {
        $rk=$this->riwayatAktif(); if(!$rk) return null;
        $sesi=DB::table('sesi_tes')->where('id_riwayat_kelas',$rk->id_riwayat_kelas)->orderByDesc('waktu_selesai')->orderByDesc('id_sesi_tes')->first(); if(!$sesi) return null;
        $rows=DB::table('hasil_tes_riasec as h')->join('dimensi_riasec as d','d.id_dimensi','=','h.id_dimensi')->where('h.id_sesi_tes',$sesi->id_sesi_tes)->where(function($q){$q->whereNotNull('h.skor_normal')->orWhereNotNull('h.skor_mentah');})->get();
        if($rows->isEmpty()) return null;
        $skor=[];$nama=[];foreach($rows as $r){$skor[$r->kode]=(float)($r->skor_normal ?? $r->skor_mentah ?? 0);$nama[$r->kode]=$r->nama;} arsort($skor);
        $dua=array_slice(array_keys($skor),0,2);$tanggal=$sesi->waktu_selesai ?: $sesi->waktu_mulai;
        return (object)['skor'=>$skor,'nama'=>$nama,'kode'=>$sesi->kode_holland ?: implode('',$dua),'judul'=>implode(' & ',array_map(fn($k)=>$nama[$k],$dua)),
            'diperbarui'=>$tanggal?Carbon::parse($tanggal):null,'oleh'=>'—','tag'=>[]];
    }

    protected function dataTes(): ?object
    {
        $rk=$this->riwayatAktif(); if(!$rk) return null;
        $s=DB::table('sesi_tes')->where('id_riwayat_kelas',$rk->id_riwayat_kelas)->orderByDesc('id_sesi_tes')->first(); if(!$s)return null;
        $tanggal=$s->waktu_selesai ?: $s->waktu_mulai;
        return (object)['status'=>$s->waktu_selesai?'Selesai':'Sedang berlangsung','tanggal'=>$tanggal?Carbon::parse($tanggal):null,'penyelenggara'=>'—'];
    }

    protected function dataBk(): ?object
    {
        if(!$this->siswaId()) return null;
        $r=DB::table('permintaan_konsultasi')->where('id_siswa',$this->siswaId())->orderByDesc('created_at')->first(); if(!$r)return null;
        return (object)['nama'=>'Layanan BK','status'=>$r->status ?: 'Tercatat','tanggal'=>Carbon::parse($r->created_at),'catatan'=>$r->catatan ?: '—'];
    }

    protected function dataRekomendasiProdi(): array
    {
        $rk=$this->riwayatAktif(); if(!$rk)return [];
        $s=DB::table('sesi_tes')->where('id_riwayat_kelas',$rk->id_riwayat_kelas)->orderByDesc('id_sesi_tes')->first(); if(!$s)return [];
        return DB::table('hasil_tes_riasec as h')->join('dimensi_riasec as d','d.id_dimensi','=','h.id_dimensi')->join('program_studi as p','p.id_dimensi','=','d.id_dimensi')
            ->where('h.id_sesi_tes',$s->id_sesi_tes)->where(function($q){$q->whereNotNull('h.skor_normal')->orWhereNotNull('h.skor_mentah');})->select('d.nama as rumpun',DB::raw('COALESCE(h.skor_normal,h.skor_mentah) as cocok'),'p.nama as prodi')->orderByDesc('h.skor_normal')->get()
            ->groupBy('rumpun')->map(fn($g,$nama)=>(object)['rumpun'=>$nama,'cocok'=>(float)$g->first()->cocok,'prodi'=>$g->pluck('prodi')->all(),'alasan'=>null])->values()->all();
    }

    /** Kolom kalender diisi hanya dari presensi yang benar-benar tersimpan. */
    protected function logHarian(Carbon $d, Carbon $hariIni): array
    {
        $base=['tanggal_label'=>$d->copy()->locale('id')->translatedFormat('l, j F Y'),'status'=>'belum','masuk'=>null,'pulang'=>null,'keterangan'=>null,'gerbang'=>null,'suhu'=>null,'metode'=>null,'catatan'=>null];
        if($d->isWeekend()) return ['status'=>'libur','keterangan'=>'Libur akhir pekan']+$base;
        if($d->gt($hariIni)) return $base;
        $r=$this->siswaId()?DB::table('presensi')->where('id_siswa',$this->siswaId())->whereDate('tanggal',$d->toDateString())->first():null;
        if(!$r)return $base;
        $status=match($r->status){'Hadir'=>'hadir','Terlambat'=>'toleransi','Sakit'=>'sakit','Izin'=>'izin','Alpa'=>'alpha',default=>'belum'};
        return ['status'=>$status,'masuk'=>$r->waktu_presensi?Carbon::parse($r->waktu_presensi)->format('H.i'):null,'keterangan'=>$r->status,'metode'=>$r->metode]+$base;
    }
}
