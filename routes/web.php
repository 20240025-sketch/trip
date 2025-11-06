<?php

use Illuminate\Support\Facades\Route;

// SPA catchall route
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

