<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\App\Http\Controllers\API\BlogApiController;
use Modules\CRM\App\Http\Controllers\API\WarrLeadController;
use Modules\CRM\App\Http\Controllers\API\WarrServicePageApiController;

// Public API routes (no auth required)
Route::prefix('v1')->group(function () {
    Route::get('/blogs', [BlogApiController::class, 'index']);
    Route::get('/blogs/{slug}', [BlogApiController::class, 'show']);

    Route::post('/warr-leads', [WarrLeadController::class, 'store']);

    Route::get('/warr-service-pages', [WarrServicePageApiController::class, 'serviceSlugSitemap']);
    Route::get('/warr-service-pages/{slug}', [WarrServicePageApiController::class, 'showBySlug']);
});

