<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CsrfController extends Controller
{
    /** FE mengambil token ini lalu mengirimkannya di header X-CSRF-TOKEN. */
    public function __invoke(): JsonResponse
    {
        return response()->json(['csrf_token' => csrf_token()]);
    }
}
