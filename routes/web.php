<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controller\Testtest;

Route::get('/', function () {
    return view('welcome');
});

///------


Route::get('/', Testtest::class);
Route::post('items', Testtest::class);
Route::delete('items', Testtest::class);

