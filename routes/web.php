<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/multable/{number?}', function ($number = 7) {
    $k = $number;
    return view('multable', compact("k") );
});
Route::get('/even', function () {
    return view('even');
});
Route::get('/prime', function () {
    return view('prime');
});
Route::get('/test', function () {
    return view('test');
});
