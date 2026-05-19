<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    'auth',
    'verified',
    'module.enabled:CRM'
])->prefix('crm')->group(function () {

    Route::get('/', function () {

        return view('crm::index');

    });

});