<?php

use Illuminate\Support\Facades\Route;

Route::get('/{view?}', function () {
    return view('helios::layout');
})->where('view', '.*')->name('dashboard');