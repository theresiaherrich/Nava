<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

/**
 * Bentuk respons seragam:
 *   sukses  : { data, message }
 *   daftar  : { data: [...], meta: { current_page, per_page, total, last_page }, message }
 *   error   : { message, errors?, code? }  (lihat bootstrap/app.php)
 */
abstract class Controller
{
    protected function ok(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $data, 'message' => $message], $status);
    }

    protected function created(mixed $data = null, string $message = 'Data berhasil dibuat.'): JsonResponse
    {
        return $this->ok($data, $message, 201);
    }

    protected function paginated(LengthAwarePaginator $page, ?callable $map = null, ?string $message = null): JsonResponse
    {
        $items = collect($page->items());

        return response()->json([
            'data' => ($map ? $items->map($map) : $items)->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
            'message' => $message,
        ]);
    }

    protected function perPage(): int
    {
        return max(1, min(100, (int) request('per_page', 20)));
    }
}
