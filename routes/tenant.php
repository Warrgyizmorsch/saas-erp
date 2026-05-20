<?php

use Illuminate\Support\Facades\Route;

// Public tenant profile page
Route::get('/', function () {

    return view('tenantprofile', [
        'tenant' => tenant()
    ]);

});

// Protected routes
Route::middleware([
    'auth',
    'verified'
])->group(function () {

    Route::get('/dashboard', function () {

        return view('dashboard');

    })->name('dashboard');

});