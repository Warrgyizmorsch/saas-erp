<?php

use Illuminate\Support\Facades\Route;

use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    'auth',
    'verified'
])->group(function () {

    Route::get('/dashboard', function () {

        return view('shared::dashboard');

    });

});