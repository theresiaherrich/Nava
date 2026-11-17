<?php

namespace App\Support\Dummy;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Pagination & filter sisi server untuk data dummy, meniru respons API
 * `{ data, meta: { current_page, per_page, total } }`.
 */
class Paginasi
{
    public static function buat(Collection $items, Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $halaman = max(1, (int) $request->query('page', 1));
        $halaman = min($halaman, max(1, (int) ceil($items->count() / $perPage)));

        return (new LengthAwarePaginator(
            $items->forPage($halaman, $perPage)->values(),
            $items->count(),
            $perPage,
            $halaman,
            ['path' => $request->url(), 'pageName' => 'page'],
        ))->withQueryString();
    }

    /** Saring koleksi: kunci filter => nilai dari query string (kosong/"semua" diabaikan). */
    public static function saring(Collection $items, array $aturan): Collection
    {
        foreach ($aturan as $nilai => $cek) {
            if ($nilai === null || $nilai === '' || $nilai === 'semua') {
                continue;
            }
            $items = $items->filter(fn ($row) => $cek($row, $nilai));
        }

        return $items->values();
    }

    public static function cocok(?string $q, string ...$teks): bool
    {
        $q = mb_strtolower(trim((string) $q));

        return $q === '' || collect($teks)->contains(fn ($t) => str_contains(mb_strtolower($t), $q));
    }
}
