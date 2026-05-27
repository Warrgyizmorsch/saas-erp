<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Modules\CRM\App\Http\Controllers\CategoryController;
use Modules\CRM\App\Http\Controllers\BlogController;

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
use Modules\Shared\App\Http\Controllers\UserController;

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
    'check.permission'
])->prefix('crm')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('crm.dashboard');

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

    Route::get('/modern-leads', [NewleadController::class, 'index'])->name('modern.leads.index');
    Route::post('/modern-leads/quick-update/{lead}', [NewleadController::class, 'updateQuick'])->name('lead.updateQuick');
    Route::post('/modern-leads/todo/{lead}', [NewleadController::class, 'storeTodo'])->name('lead.storeTodo');
    Route::get('/user/activity', [UserController::class, 'activity'])->name('user.activity');
    Route::post('/save-work-time', [UserController::class, 'saveWorkTime'])->name('save.work.time');
    Route::post('lead/bucket/get-sub-status', [LeadController::class, 'getSubStatus'])->name('lead.getSubStatus');

    Route::get('/follow-up-data', [LeadController::class, 'followUpData'])->name('lead.followUpData');
    Route::post('/callback-update/{id}', [LeadController::class, 'callbackUpdate'])->name('lead.callbackUpdate');
    Route::post('/callback-done', [LeadController::class, 'callbackDone'])->name('lead.callbackDone');
    Route::get('/lead/new-daily-report', [LeadController::class, 'newdailyReport'])->name('lead.newdailyReport');

    Route::get('/campaign-performance', [LeadController::class, 'campaignPerformance'])->name('lead.campaignPerformance');
    Route::get('/source', [LeadController::class, 'sourcePerformance'])->name('lead.sourcePerformance');
    Route::get('/lead/counsellor-report', [LeadController::class, 'councillorReport'])->name('lead.councillorReport');
    Route::get('/fetch-templates', [LeadController::class, 'fetchTemplates'])->name('lead.fetchTemplates');
    Route::post('/send-sms', [LeadController::class, 'sendSMS'])->name('lead.sendSms');
    Route::post('/leads/bulk-delete', [LeadController::class, 'bulkDelete'])->name('leads.bulkDelete');


    Route::get('/leads-export', [LeadController::class, 'exportLeads'])
        ->name('leads.export');

    Route::get('/lead/activity', [LeadController::class, 'leadActivity'])->name('lead.leadActivity');

    Route::get('/leads/export', [LeadController::class, 'export'])->name('lead.export');

    Route::get('/user/search-by-mobile', [LeadController::class, 'searchByMobile'])->name('user.search.byMobile');
});