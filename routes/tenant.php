<?php

use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified'
])->group(function () {

    Route::get('/', function () {

        return view('tenantprofile', [
            'tenant' => tenant()
        ]);

    });

    Route::get('/dashboard', function () {

        return view('dashboard');

    })->name('dashboard');

});