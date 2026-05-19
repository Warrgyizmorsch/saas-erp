<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    'auth',
    'verified',
    'module.enabled:HRMS'
])->prefix('hrms')->group(function () {

    Route::get('/', function () {

        return view('hrms::index');

    });

});