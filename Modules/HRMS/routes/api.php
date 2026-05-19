<?php

use Illuminate\Support\Facades\Route;
use Modules\HRMS\Http\Controllers\HRMSController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('hrms', HRMSController::class)->names('hrms');
});
