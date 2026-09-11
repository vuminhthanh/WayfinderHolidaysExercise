<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'service' => 'Wayfinder Holidays Laravel Exercise',
    'status' => 'ok',
]));
