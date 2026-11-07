<?php

namespace App\Http\Controllers\A3_BK;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/** Student-facing A3 screens. This controller does not generate recommendation scores. */
class StudentXController extends Controller
{
    private function student(): object
    {
        abort_unless(auth()->check() && strtoupper((string) auth()->user()->role) === 'SISWA', 403);
        $student = DB::table('siswa')->where('id_user', auth()->id())->first();
        abort_unless($student, 403, 'Akun ini belum terhubung ke data siswa.');
        return $student;
    }

    private function context(): array
    {
        $student = $this->student();
        $class = DB::table('riwayat_kelas as rk')->join('kelas as k', 'rk.id_kelas', '=', 'k.id_kelas')
            ->where('rk.id_siswa', $student->id_siswa)
            ->orderByDesc('k.tahun_ajaran')->orderByDesc('rk.id_riwayat_kelas')
            ->select('rk.id_riwayat_kelas', 'k.nama', 'k.tingkat', 'k.tahun_ajaran')->first();
        abort_unless($class && $class->tingkat === 'X', 403, 'Halaman ini khusus siswa kelas X.');
        return compact('student', 'class');
    }

    private function sessions(int $classHistoryId)
    {
        return DB::table('sesi_tes')->where('id_riwayat_kelas', $classHistoryId)
            ->whereNotNull('waktu_selesai')->orderByDesc('waktu_selesai')->get();
    }

    private function resultsFor(int $sessionId): array
    {
        return DB::table('hasil_tes_riasec as h')
            ->join('dimensi_riasec as d', 'd.id_dimensi', '=', 'h.id_dimensi')
            ->where('h.id_sesi_tes', $sessionId)->pluck('h.skor_normal', 'd.kode')->toArray();
    }

    public function dashboard()
    {
        $ctx = $this->context();
        $last = $this->sessions($ctx['class']->id_riwayat_kelas)->first();
        $scores = $last ? $this->resultsFor($last->id_sesi_tes) : [];
        $requests = DB::table('permintaan_konsultasi')->where('id_siswa', $ctx['student']->id_siswa)->count();
        return view('bk.student-x.dashboard', $ctx + compact('last', 'scores', 'requests'));
    }

    public function instructions()
    {
        return view('bk.student-x.instructions', $this->context());
    }

    public function questions()
    {
        $ctx = $this->context();
        $questions = DB::table('soal_riasec')->orderBy('urutan')->orderBy('id_soal_riasec')->get();
        return view('bk.student-x.questions', $ctx + compact('questions'));
    }

    public function submit(Request $request)
    {
        $ctx = $this->context();
        $questions = DB::table('soal_riasec')->orderBy('urutan')->get();
        abort_if($questions->isEmpty(), 422, 'Belum ada soal tes aktif.');
        $answerRules = ['jawaban' => ['required', 'array', 'size:'.$questions->count()]];
        foreach ($questions as $question) {
            $answerRules['jawaban.'.$question->id_soal_riasec] = ['required', 'integer', 'between:1,5'];
        }
        $validated = Validator::make($request->all(), $answerRules)->validate();
        $answers = $validated['jawaban'];
        // Preserve historical sessions; do not use the shared demo session #1.
        $sessionId = DB::transaction(function () use ($ctx, $questions, $answers) {
            $sessionId = DB::table('sesi_tes')->insertGetId([
                'id_riwayat_kelas' => $ctx['class']->id_riwayat_kelas,
                'tingkat' => 'X', 'waktu_mulai' => now(), 'waktu_selesai' => now(),
            ], 'id_sesi_tes');
            $dimensions = DB::table('dimensi_riasec')->pluck('kode', 'id_dimensi');
            $totals = []; $counts = [];
            foreach ($questions as $q) {
                $value = (int) $answers[$q->id_soal_riasec];
                DB::table('jawaban_riasec')->insert([
                    'id_sesi_tes' => $sessionId, 'id_soal_riasec' => $q->id_soal_riasec, 'nilai' => $value,
                ]);
                $id = $q->id_dimensi;
                $totals[$id] = ($totals[$id] ?? 0) + $value;
                $counts[$id] = ($counts[$id] ?? 0) + 1;
            }
            foreach ($totals as $dim => $total) {
                DB::table('hasil_tes_riasec')->insert([
                    'id_sesi_tes' => $sessionId, 'id_dimensi' => $dim,
                    'skor_mentah' => $total, 'skor_normal' => round($total / ($counts[$dim] * 5) * 100, 2),
                ]);
            }
            $ranked = [];
            foreach ($totals as $dim => $total) {
                if (isset($dimensions[$dim])) $ranked[] = ['code' => $dimensions[$dim], 'normal' => $total / ($counts[$dim] * 5)];
            }
            usort($ranked, fn ($a, $b) => ($b['normal'] <=> $a['normal']) ?: strcmp($a['code'], $b['code']));
            DB::table('sesi_tes')->where('id_sesi_tes', $sessionId)
                ->update(['kode_holland' => implode('', array_column(array_slice($ranked, 0, 3), 'code'))]);
            return $sessionId;
        });
        return redirect()->route('a3.x.result', $sessionId)->with('success', 'Jawaban berhasil disimpan.');
    }

    public function result(int $sessionId)
    {
        $ctx = $this->context();
        $session = DB::table('sesi_tes')->where('id_sesi_tes', $sessionId)
            ->where('id_riwayat_kelas', $ctx['class']->id_riwayat_kelas)
            ->whereNotNull('waktu_selesai')->first();
        abort_unless($session, 404);
        $scores = $this->resultsFor($sessionId);
        return view('bk.student-x.result', $ctx + compact('session', 'scores'));
    }

    public function latestResult()
    {
        $ctx = $this->context();
        $last = $this->sessions($ctx['class']->id_riwayat_kelas)->first();
        return $last ? redirect()->route('a3.x.result', $last->id_sesi_tes) : redirect()->route('a3.x.instructions')->with('info', 'Belum ada hasil tes.');
    }

    public function history()
    {
        $ctx = $this->context();
        // Includes all history of this student, including earlier academic years.
        $history = DB::table('sesi_tes as s')->join('riwayat_kelas as rk', 'rk.id_riwayat_kelas', '=', 's.id_riwayat_kelas')
            ->join('kelas as k', 'k.id_kelas', '=', 'rk.id_kelas')
            ->where('rk.id_siswa', $ctx['student']->id_siswa)->whereNotNull('s.waktu_selesai')
            ->select('s.*', 'k.nama as nama_kelas', 'k.tahun_ajaran')->orderByDesc('s.waktu_selesai')->get();
        return view('bk.student-x.history', $ctx + compact('history'));
    }

    public function recommendations()
    {
        // No recommendation contract/table exists yet. Never display fictional percentages as calculated results.
        return view('bk.student-x.recommendations', $this->context());
    }

    public function counseling()
    {
        $ctx = $this->context();
        $requests = DB::table('permintaan_konsultasi')->where('id_siswa', $ctx['student']->id_siswa)
            ->orderByDesc('created_at')->orderByDesc('id_permintaan')->get();
        return view('bk.student-x.counseling', $ctx + compact('requests'));
    }

    public function storeCounseling(Request $request)
    {
        $ctx = $this->context();
        $validated = $request->validate([
            'topik' => ['required', 'string', 'max:100'],
            'deskripsi' => ['required', 'string', 'min:20', 'max:500'],
        ]);
        $active = DB::table('permintaan_konsultasi')->where('id_siswa', $ctx['student']->id_siswa)
            ->whereIn('status', ['Menunggu', 'Diproses', 'Dijadwalkan'])->exists();
        if ($active) return back()->withErrors(['topik' => 'Selesaikan pengajuan aktif sebelum mengirim pengajuan baru.'])->withInput();
        // Existing table has no separate topic/reply/schedule fields; retain text in catatan.
        DB::table('permintaan_konsultasi')->insert([
            'id_siswa' => $ctx['student']->id_siswa,
            'status' => 'Menunggu',
            'catatan' => '[Topik] '.$validated['topik']."\n".$validated['deskripsi'],
            'created_at' => now(),
        ]);
        return redirect()->route('a3.x.counseling')->with('success', 'Pengajuan bimbingan berhasil dikirim.');
    }
}
