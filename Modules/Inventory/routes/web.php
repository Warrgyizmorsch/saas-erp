<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    'auth',
    'verified',
    'module.enabled:Inventory'
])->prefix('inventory')->group(function () {

    Route::get('/', function () {

        return view('inventory::index');

    });

});