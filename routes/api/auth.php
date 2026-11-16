<?php

use Illuminate\Support\Facades\Route;

Route::get('/csrf-token', fn () => response()->json(['data' => ['csrf_token' => csrf_token()], 'message' => null]));
