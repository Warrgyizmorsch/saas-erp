<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Modules\CRM\App\Http\Controllers\CategoryController;
use Modules\CRM\App\Http\Controllers\BlogController;
use Modules\CRM\App\Http\Controllers\UserController;
use Modules\CRM\App\Http\Controllers\RouteController;
use Modules\CRM\App\Http\Controllers\RoleController;
use Modules\CRM\App\Http\Controllers\MenuController;
use Modules\CRM\App\Http\Controllers\RolePermissionController;
use Modules\CRM\App\Http\Controllers\UserPermissionController;
use Modules\CRM\App\Http\Controllers\BucketController;
use Modules\CRM\App\Http\Controllers\LeadController;
use Modules\CRM\App\Http\Controllers\LeadQuestionController;
use Modules\CRM\App\Http\Controllers\LeadSourceController;
use Modules\CRM\App\Http\Controllers\WarrLeadController;
use Modules\CRM\App\Http\Controllers\WarrServicePageController;
use Modules\CRM\App\Http\Controllers\SubjectPageController;
use Modules\CRM\App\Http\Controllers\NewleadController;
use Modules\CRM\App\Http\Controllers\UniversityDetailController;
use Modules\CRM\App\Http\Controllers\WhatsAppController;
use Modules\CRM\App\Http\Controllers\DashboardController;

// Public routes (without auth)
Route::get('/send-whatsapp-all', [WhatsAppController::class, 'sendAll'])
    ->name('send.whatsapp.all');

Route::get('/get-leads-by-type', [LeadController::class, 'getLeadsByType'])
    ->name('get.leads.by.type');

Route::get('/get-user-report-data', [LeadController::class, 'getUserReportData'])
    ->name('get.user.report.data');

Route::get('/get-lead-transitions', [LeadController::class, 'getLeadTransitions'])
    ->name('get.lead.transitions');

Route::post('/lead/bulk-owner-update', [LeadController::class, 'bulkOwnerUpdate'])
    ->name('lead.bulkOwnerUpdate');

Route::post('/crm/leads/import', [LeadController::class, 'import'])
    ->name('lead.import');

Route::get('/crm/leads/sample', [LeadController::class, 'downloadSample'])
    ->name('lead.sample');

Route::get('/lead-import-status/{jobId}', [LeadController::class, 'getImportJobStatus'])
    ->name('lead.importStatus');

// Protected routes (with auth & permission check)
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    'auth',
    'verified',
    'module.enabled:CRM',
    // 'check.permission'
])->prefix('crm')->group(function () {

    Route::get('/', function () {
        return view('crm::index');
    });

    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])
            ->name('category.index');
        Route::post('/store', [CategoryController::class, 'store'])
            ->name('category.store');
        Route::get('/edit/{id}', [CategoryController::class, 'edit'])
            ->name('category.edit');
        Route::put('/update/{id}', [CategoryController::class, 'update'])
            ->name('category.update');
        Route::delete('/destroy/{id}', [CategoryController::class, 'destroy'])
            ->name('category.destroy');
        Route::post('/recover/{id}', [CategoryController::class, 'recover'])
            ->name('category.recover');
    });

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/store', [UserController::class, 'store'])->name('store');
        Route::get('/edit/{user}', [UserController::class, 'edit'])->name('edit');
        Route::put('/update/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/destroy/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::get('/session', [UserController::class, 'indexLog'])->name('session');
        Route::post('/{user}/logout', [UserController::class, 'forceLogout'])->name('logout');
        Route::get('/{user}/history', [UserController::class, 'userHistory'])->name('history');
        Route::get('/{user}/lead-history', [UserController::class, 'leadHistory'])->name('leadHistory');
    });

    // Routes management
    Route::resource('routes', RouteController::class);

    // Roles
    Route::resource('roles', RoleController::class);

    // Menus
    Route::resource('menus', MenuController::class);

    // Role permissions
    Route::resource('role-permissions', RolePermissionController::class);
    Route::post('roles/{role}/permissions', [RolePermissionController::class, 'updatePermissions'])
        ->name('role-permissions-id.update');

    // User permissions
    Route::resource('user-permissions', UserPermissionController::class);
    Route::post('users/{user}/permissions', [UserPermissionController::class, 'updatePermissions'])
        ->name('user-permissions-id.update');

    // Leads
    Route::prefix('lead')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('lead.index');
        Route::get('/create', [LeadController::class, 'create'])->name('lead.create');
        Route::post('/store', [LeadController::class, 'store'])->name('lead.store');
        Route::get('/edit/{lead}', [LeadController::class, 'edit'])->name('lead.edit');
        Route::put('/update/{lead}', [LeadController::class, 'update'])->name('lead.update');
        Route::put('/bucket/{lead}', [LeadController::class, 'updateBucket'])->name('lead.updateBucket');
        Route::put('/status/{lead}', [LeadController::class, 'updateStatus'])->name('lead.updateStatus');
        Route::get('/history/{lead}', [LeadController::class, 'history'])->name('lead.history');
        Route::post('/send-message', [LeadController::class, 'sendMessage'])->name('lead.sendMessage');
        Route::get('/daily-report', [LeadController::class, 'dailyReport'])->name('lead.dailyReport');
        Route::put('/{lead}/engagement-status', [LeadController::class, 'updateEngagementStatus'])
            ->name('lead.updateEngagementStatus');
        Route::get('/application', [LeadController::class, 'application'])->name('lead.application');
    });

    // Buckets
    Route::prefix('bucket')->group(function () {
        Route::get('/', [BucketController::class, 'index'])->name('bucket.index');
        Route::post('/store', [BucketController::class, 'store'])->name('bucket.store');
        Route::get('/edit/{id}', [BucketController::class, 'edit'])->name('bucket.edit');
        Route::put('/update/{bucket}', [BucketController::class, 'update'])->name('bucket.update');
        Route::delete('/destroy/{bucket}', [BucketController::class, 'destroy'])->name('bucket.destroy');
    });

    // Lead questions
    Route::prefix('lead-questions')->group(function () {
        Route::get('/', [LeadQuestionController::class, 'index'])->name('lead_questions.index');
        Route::post('/store', [LeadQuestionController::class, 'store'])->name('lead_questions.store');
        Route::put('/update/{question}', [LeadQuestionController::class, 'update'])->name('lead_questions.update');
        Route::delete('/destroy/{question}', [LeadQuestionController::class, 'destroy'])->name('lead_questions.destroy');
        Route::put('/toggle/{question}', [LeadQuestionController::class, 'toggle'])->name('lead_questions.toggle');
    });

    // Lead sources
    Route::prefix('lead-sources')->group(function () {
        Route::get('/', [LeadSourceController::class, 'index'])->name('lead_sources.index');
        Route::post('/store', [LeadSourceController::class, 'store'])->name('lead_sources.store');
        Route::put('/update/{source}', [LeadSourceController::class, 'update'])->name('lead_sources.update');
        Route::put('/toggle/{source}', [LeadSourceController::class, 'toggle'])->name('lead_sources.toggle');
    });

    // Blog
    Route::prefix('crm-blog')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('blog.index');
        Route::get('/create', [BlogController::class, 'create'])->name('blog.create');
        Route::post('/store', [BlogController::class, 'store'])->name('blog.store');
        Route::get('/edit/{id}', [BlogController::class, 'edit'])->name('blog.edit');
        Route::put('/update/{id}', [BlogController::class, 'update'])->name('blog.update');
        Route::delete('/destroy/{id}', [BlogController::class, 'destroy'])->name('blog.destroy');
    });

    // Authors
    Route::prefix('author')->name('author.')->group(function () {
        Route::get('/', [BlogController::class, 'blogAuthor'])->name('index');
        Route::post('/store', [BlogController::class, 'authorstore'])->name('store');
        Route::get('/edit/{id}', [BlogController::class, 'authorEdit'])->name('edit');
        Route::delete('/destroy/{id}', [BlogController::class, 'authorDestroy'])->name('destroy');
    });

    // Warr Leads
    Route::prefix('warr-leads')->group(function () {
        Route::get('/', [WarrLeadController::class, 'index'])->name('warr-leads.index');
        Route::put('/{lead}', [WarrLeadController::class, 'update'])->name('warr-leads.updateWarrLead');
    });

    // Warr Service Pages
    Route::prefix('warr-service-pages')->group(function () {
        Route::get('/', [WarrServicePageController::class, 'index'])->name('warr-service-pages.index');
        Route::get('/create', [WarrServicePageController::class, 'create'])->name('warr-service-pages.create');
        Route::post('/store', [WarrServicePageController::class, 'store'])->name('warr-service-pages.store');
        Route::get('/edit/{id}', [WarrServicePageController::class, 'edit'])->name('warr-service-pages.edit');
        Route::post('/update/{id}', [WarrServicePageController::class, 'update'])->name('warr-service-pages.update');
        Route::delete('/delete/{id}', [WarrServicePageController::class, 'destroy'])->name('warr-service-pages.delete');
        Route::get('/cities', [WarrServicePageController::class, 'getCities'])->name('warr-service-pages.cities');
    });

    // Warr CRUD
    Route::prefix('warr-crud')->group(function () {
        // Countries
        Route::get('/countries', [WarrServicePageController::class, 'countriesIndex'])->name('warr-countries.index');
        Route::post('/countries', [WarrServicePageController::class, 'countriesStore'])->name('warr-countries.store');
        Route::delete('/countries/{id}', [WarrServicePageController::class, 'countriesDestroy'])->name('warr-countries.destroy');

        // Cities
        Route::get('/cities', [WarrServicePageController::class, 'citiesIndex'])->name('warr-cities.index');
        Route::post('/cities', [WarrServicePageController::class, 'citiesStore'])->name('warr-cities.store');
        Route::delete('/cities/{id}', [WarrServicePageController::class, 'citiesDestroy'])->name('warr-cities.destroy');

        // Services
        Route::get('/services', [WarrServicePageController::class, 'servicesIndex'])->name('warr-services.index');
        Route::post('/services', [WarrServicePageController::class, 'servicesStore'])->name('warr-services.store');
        Route::delete('/services/{id}', [WarrServicePageController::class, 'servicesDestroy'])->name('warr-services.destroy');
    });

    // Subject Pages
    Route::prefix('crm-subject-pages')->group(function () {
        Route::get('/', [SubjectPageController::class, 'index'])->name('crm-subject-pages.index');
        Route::get('/create', [SubjectPageController::class, 'create'])->name('crm-subject-pages.create');
        Route::post('/store', [SubjectPageController::class, 'store'])->name('crm-subject-pages.store');
        Route::get('/edit/{id}', [SubjectPageController::class, 'edit'])->name('crm-subject-pages.edit');
        Route::put('/update/{id}', [SubjectPageController::class, 'update'])->name('crm-subject-pages.update');
        Route::delete('/destroy/{id}', [SubjectPageController::class, 'destroy'])->name('crm-subject-pages.destroy');
    });
});