<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * Tujuan sementara untuk aksi yang dirujuk view tetapi belum punya logika
 * (mis. ekspor DKN, pratinjau pelengkap rapor). Diganti saat API tersambung.
 */
class BelumTersediaController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return back()->with('toast', [
            'type' => 'info',
            'message' => 'Fitur ini belum tersambung ke backend.',
        ]);
    }
}
