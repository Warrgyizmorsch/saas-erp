<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

use Modules\Inventory\App\Http\Controllers\ApprovalController;
use Modules\Inventory\App\Http\Controllers\CategoryController;
use Modules\Inventory\App\Http\Controllers\ChatbotController;
use Modules\Inventory\App\Http\Controllers\ConsumptionController;
use Modules\Inventory\App\Http\Controllers\CurrentStockController;
use Modules\Inventory\App\Http\Controllers\DashboardController;
use Modules\Inventory\App\Http\Controllers\DepartmentController;
use Modules\Inventory\App\Http\Controllers\EmployeeDashboardController;
use Modules\Inventory\App\Http\Controllers\GrnController;
use Modules\Inventory\App\Http\Controllers\InventoryAvailableController;
use Modules\Inventory\App\Http\Controllers\InventoryController;
use Modules\Inventory\App\Http\Controllers\IssueController;
use Modules\Inventory\App\Http\Controllers\JobCardController;
use Modules\Inventory\App\Http\Controllers\NotificationController;
use Modules\Inventory\App\Http\Controllers\PlacementController;
use Modules\Inventory\App\Http\Controllers\ProductController;
use Modules\Inventory\App\Http\Controllers\ProjectController;
use Modules\Inventory\App\Http\Controllers\PurchaseRequestController;
use Modules\Inventory\App\Http\Controllers\PurchaseOrderController;
use Modules\Inventory\App\Http\Controllers\RsRequestSlipController;
use Modules\Inventory\App\Http\Controllers\StageController;
use Modules\Inventory\App\Http\Controllers\StageStatusController;
use Modules\Inventory\App\Http\Controllers\SupplierController;
use Modules\Inventory\App\Http\Controllers\SupplierInventoryController;
use Modules\Inventory\App\Http\Controllers\UnitController;
use Modules\Inventory\App\Http\Controllers\VendorController;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    'auth',
    'verified',
    'module.enabled:Inventory',
    'check.permission'
])->prefix('inventory')->group(function () {

    // Main / Dashboard Routes
    Route::get('/', [DashboardController::class, 'index'])->name('inventory.dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/employee-dashboard', [EmployeeDashboardController::class, 'index'])->name('employeeDashboard');
    Route::get('/stock-ledger', [CurrentStockController::class, 'stockLedger'])->name('stockLedger');
    Route::get('/stock/redirect/{id}', [CurrentStockController::class, 'redirectToSource'])->name('stock.redirect');
    
    // Exports
    Route::get('/purchase-order-export', [PurchaseOrderController::class, 'exportPO'])->name('purchase-order.export');
    Route::get('/required-vs-available/export', [InventoryController::class, 'exportRequiredVsAvailable'])->name('require-vs-available.export');
    Route::get('/current-stock/export', [CurrentStockController::class, 'currentExport'])->name('current-stock.export');
    Route::delete('/purchase-order/transaction/{id}', [PurchaseOrderController::class, 'deleteAdvance'])->name('purchase-order.deleteTransaction');

    // Product Samples
    Route::get('/product/sample-download', [ProductController::class, 'downloadSample'])->name('product.sample.download');
    
    // Project View
    Route::get('/project/{id}', [ProjectController::class, 'show'])->name('project.show');

    // Stages
    Route::get('/stages', [StageController::class, 'index'])->name('stages.index');
    Route::get('/stages/create', [StageController::class, 'create'])->name('stages.create');
    Route::post('/stages/store', [StageController::class, 'store'])->name('stages.store');
    Route::get('/stages/edit', [StageController::class, 'edit'])->name('stages.edit');
    Route::post('/stages/update', [StageController::class, 'update'])->name('stages.update');
    Route::delete('/stages/delete/{id}', [StageController::class, 'destroy'])->name('stages.delete');
    Route::get('/stages/by-section', [StageController::class, 'getBySection']);

    // Stage Statuses
    Route::get('/stage-status', [StageStatusController::class, 'index'])->name('stage-status.index');
    Route::post('/stage-status/store', [StageStatusController::class, 'store'])->name('stage-status.store');
    Route::get('/stage-status/{id}/edit', [StageStatusController::class, 'edit'])->name('stage-status.edit');
    Route::put('/stage-status/{id}/update', [StageStatusController::class, 'update'])->name('stage-status.update');
    Route::delete('/stage-status/{id}', [StageStatusController::class, 'destroy'])->name('stage-status.destroy');

    Route::get('/project/stage/{id}', [ProjectController::class, 'projectstage'])->name('project.stage');
    Route::post('/stage/update-parent-status', [StageController::class, 'updateParentStatus']);
    Route::post('/stage/update-sub-status', [StageController::class, 'updateSubStatus']);
    Route::post('/project/{id}/flow-update', [ProjectController::class, 'updateFlow'])->name('project.updateFlow');
    
    // Project stage timeline & documents routes
    Route::post('/projects/{project}/documents', [ProjectController::class, 'uploadDocuments'])->name('project.documents.upload');
    Route::get('/project-documents/{document}/view', [ProjectController::class, 'viewDocument'])->name('project.documents.view');
    Route::get('/project-documents/{document}/download', [ProjectController::class, 'downloadDocument'])->name('project.documents.download');
    Route::delete('/project-documents/{document}', [ProjectController::class, 'deleteDocument'])->name('project.documents.delete');
    Route::post('/project-stage-timeline/update', [ProjectController::class, 'updateTimeline'])->name('project.stage.timeline.update');

    // Available Stock and Supplier Inventory
    Route::get('/available-stock/{inventory}', [InventoryAvailableController::class, 'availableStock']);
    Route::prefix('supplier-inventory')->group(function () {
        Route::post('/store', [SupplierInventoryController::class, 'store'])->name('supplier_inventory.store');
    });

    // Departments Resource
    Route::resource('departments', DepartmentController::class);

    // Issue Slip
    Route::group([], function () {
        Route::get('issue', [IssueController::class, 'index'])->name('issue.index');
        Route::get('issue/view-list', [IssueController::class, 'viewList'])->name('issue.view-list');
        Route::get('issue/create', [IssueController::class, 'create'])->name('issue.create');
        Route::post('issue/store', [IssueController::class, 'store'])->name('issue.store');
        Route::get('issue/{id}/edit', [IssueController::class, 'edit'])->name('issue.edit');
        Route::put('issue/{id}', [IssueController::class, 'update'])->name('issue.update');
        Route::get('issue/{id}', [IssueController::class, 'show'])->name('issue.show');
        Route::delete('issue/{id}', [IssueController::class, 'destroy'])->name('issue.destroy');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications')->group(function () {
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('.read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('.delete');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('.readAll');
    });

    // Core Inventory Operations
    Route::prefix('purchase-order')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('purchase-order.index');
        Route::get('/create', [PurchaseOrderController::class, 'create'])->name('purchase-order.create');
        Route::post('/store', [PurchaseOrderController::class, 'store'])->name('purchase-order.store');
        Route::get('/view', [PurchaseOrderController::class, 'view'])->name('purchase-order.view');
        Route::get('/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-order.edit');
        Route::post('/{id}/update', [PurchaseOrderController::class, 'update'])->name('purchase-order.update');
        Route::get('/{id}/show', [PurchaseOrderController::class, 'show'])->name('purchase-order.show');
        Route::delete('/{id}/delete', [PurchaseOrderController::class, 'destroy'])->name('purchase-order.destroy');
        Route::post('/{id}/status-update', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-order.status-update');
        Route::get('/approval-view', [PurchaseOrderController::class, 'approvalView'])->name('purchase-order.approval');
        Route::post('/{id}/updateAdvance', [PurchaseOrderController::class, 'updateAdvance'])->name('purchase-order.updateAdvance');
        Route::patch('/{id}/delivery-status', [PurchaseOrderController::class, 'updateDeliveryStatus'])->name('purchase.updateDeliveryStatus');
    });

    Route::prefix('purchase_request')->group(function () {
        Route::get('/', [PurchaseRequestController::class, 'add'])->name('purchase_request.add');
        Route::match(['get', 'post'], '/create', [PurchaseRequestController::class, 'create'])->name('purchase_request.create');
        Route::post('/store', [PurchaseRequestController::class, 'store'])->name('purchase_request.store');
        Route::get('/approval-view', [PurchaseRequestController::class, 'view'])->name('purchase_request.approval-view');
        Route::get('/list-view', [PurchaseRequestController::class, 'listView'])->name('purchase_request.list-view');
        Route::get('/{id}/list-edit', [PurchaseRequestController::class, 'edit'])->name('purchase_request.list-edit');
        Route::post('/update/{id}', [PurchaseRequestController::class, 'update'])->name('purchase_request.update');
        Route::post('/{id}/status-update', [PurchaseRequestController::class, 'updateStatus'])->name('purchase_request.status-update');
        Route::delete('/{id}', [PurchaseRequestController::class, 'destroy'])->name('purchase_request.destroy');
        Route::get('/{id}/show-detail', [PurchaseRequestController::class, 'show'])->name('purchase_request.show-detail');
    });

    Route::prefix('job_card')->group(function () {
        Route::get('/', [JobCardController::class, 'add'])->name('job_card.add');
        Route::get('/create', [JobCardController::class, 'create'])->name('job_card.create');
        Route::post('/store', [JobCardController::class, 'store'])->name('job_card.store');
        Route::get('/view', [JobCardController::class, 'view'])->name('job_card.view');
        Route::get('{id}/edit', [JobCardController::class, 'edit'])->name('job_card.edit');
        Route::get('{id}/show', [JobCardController::class, 'show'])->name('job_card.show');
        Route::put('{id}/update', [JobCardController::class, 'update'])->name('job_card.update');
        Route::delete('/{id}/destroy', [JobCardController::class, 'destroy'])->name('job_card.destroy');
    });

    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
        Route::get('/search', [SupplierController::class, 'search'])->name('search');
        Route::get('/search-code', [SupplierController::class, 'searchCode'])->name('searchCode');
    });

    Route::prefix('placement')->group(function () {
        Route::get('/', [PlacementController::class, 'index'])->name('placement.index');
        Route::get('/create', [PlacementController::class, 'create'])->name('placement.create');
        Route::post('/store', [PlacementController::class, 'store'])->name('placement.store');
        Route::get('/edit/{id}', [PlacementController::class, 'edit'])->name('placement.edit');
        Route::put('/update/{id}', [PlacementController::class, 'update'])->name('placement.update');
        Route::delete('/delete/{id}', [PlacementController::class, 'destroy'])->name('placement.destroy');
    });

    Route::prefix('current-stock')->group(function () {
        Route::get('/', [CurrentStockController::class, 'index'])->name('current-stock.index');
    });

    Route::prefix('required-vs-available')->group(function () {
        Route::get('/', [InventoryController::class, 'requiredVsAvailable'])->name('required-vs-available.index');
    });

    Route::prefix('vendor')->group(function () {
        Route::get('/', [VendorController::class, 'index'])->name('vendor.index');
        Route::get('/create', [VendorController::class, 'create'])->name('vendor.create');
        Route::post('/store', [VendorController::class, 'store'])->name('vendor.store');
        Route::get('/edit/{id}', [VendorController::class, 'edit'])->name('vendor.edit');
        Route::put('/update/{id}', [VendorController::class, 'update'])->name('vendor.update');
        Route::delete('vendor/delete/{id}', [VendorController::class, 'destroy'])->name('vendor.destroy');
    });

    Route::prefix('units')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->name('units.index');
        Route::get('/create', [UnitController::class, 'create'])->name('units.create');
        Route::post('/store', [UnitController::class, 'store'])->name('units.store');
        Route::delete('/delete/{id}', [UnitController::class, 'destroy'])->name('units.destroy');
        Route::get('/edit/{id}', [UnitController::class, 'edit'])->name('units.edit');
        Route::put('/update/{id}', [UnitController::class, 'update'])->name('units.update');
        Route::put('/toggle/{id}', [UnitController::class, 'toggle'])->name('units.toggle');
    });

    Route::prefix('request-slip')->group(function () {
        Route::get('/products/{projectId}', [RsRequestSlipController::class, 'getProductsByProject'])->name('request-slip.products');
        Route::get('/product-items/{productId}', [RsRequestSlipController::class, 'getProductItemsByProduct'])->name('request-slip.product-items');
        Route::get('/create', [RsRequestSlipController::class, 'create'])->name('request-slip.create');
        Route::post('/store', [RsRequestSlipController::class, 'store'])->name('request-slip.store');
        Route::put('/update/{id}', [RsRequestSlipController::class, 'update'])->name('request-slip.update');
        Route::delete('/request-slip/{id}', [RsRequestSlipController::class, 'destroy'])->name('request-slip.destroy');
        Route::get('/', [RsRequestSlipController::class, 'index'])->name('request-slip.index');
        Route::get('/view-all', [RsRequestSlipController::class, 'viewAll'])->name('request-slip.view-all');
        Route::get('/{id}', [RsRequestSlipController::class, 'show'])->name('request-slip.show');
        Route::post('/approve/{id}', [RsRequestSlipController::class, 'approve'])->name('request-slip.approve');
        Route::post('/reject/{id}', [RsRequestSlipController::class, 'reject'])->name('request-slip.reject');
        Route::post('/resubmit/{id}', [RsRequestSlipController::class, 'resubmit'])->name('request-slip.resubmit');
        Route::post('/complete/{id}', [RsRequestSlipController::class, 'complete'])->name('request-slip.complete');
        Route::get('/edit/{id}', [RsRequestSlipController::class, 'edit'])->name('request-slip.edit');
        Route::get('/compare/{id}', [RsRequestSlipController::class, 'compare'])->name('request-slip.compare');
    });

    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/store', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/edit/{id}', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('/update/{id}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::POST('/delete/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::POST('/toggle/{id}', [InventoryController::class, 'toggle'])->name('inventory.toggle');
        Route::get('/add', [InventoryController::class, 'addInventory'])->name('inventory.add');
        Route::get('/search', [InventoryController::class, 'search'])->name('inventory.search');
        Route::get('/opening-stock', [InventoryController::class, 'openingStockForm'])->name('inventory.opening-stock.form');
        Route::post('/opening-stock/store', [InventoryController::class, 'storeOpeningStock'])->name('opening-stock.bulk.update');
        Route::put('/opening-stock/{id}', [InventoryController::class, 'updateOpeningStock'])->name('opening-stock.single.update');
    });

    Route::prefix('approval')->group(function () {
        Route::resource('requisition', ApprovalController::class);
        Route::post('requisition/{id}/approve', [ApprovalController::class, 'approve'])->name('requisition.approve');
        Route::post('requisition/{id}/reject', [ApprovalController::class, 'reject'])->name('requisition.reject');
        Route::post('requisition/{id}/update-status', [ApprovalController::class, 'updateStatus'])->name('requisition.update-status');
        Route::get('admin', [ApprovalController::class, 'admin'])->name('approval.admin');
    });

    Route::prefix('product')->name('product.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/store', [ProductController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [ProductController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [ProductController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [ProductController::class, 'delete'])->name('delete');
        Route::get('/restore/{id}', [ProductController::class, 'restore'])->name('restore');
        Route::get('/view/{id}', [ProductController::class, 'view'])->name('view');
        Route::get('/product/pdf/{id}', [ProductController::class, 'pdf'])->name('product.pdf');
        Route::get('/{id}/duplicate', [ProductController::class, 'duplicate'])->name('duplicate');
        Route::post('/product-import', [ProductController::class, 'import'])->name('import');
    });

    Route::prefix('project')->name('project.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::post('/store', [ProjectController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [ProjectController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [ProjectController::class, 'update'])->name('update');
        Route::post('/delete/{id}', [ProjectController::class, 'delete'])->name('delete');
        Route::post('/restore/{id}', [ProjectController::class, 'restore'])->name('restore');
    });

    Route::prefix('grn')->name('grn.')->group(function () {
        Route::get('/', [GrnController::class, 'index'])->name('index');
        Route::get('/create', [GrnController::class, 'create'])->name('create');
        Route::post('/store', [GrnController::class, 'store'])->name('store');
        Route::get('/list', [GrnController::class, 'grnList'])->name('list');
        Route::get('/{id}', [GrnController::class, 'show'])->name('show');
        Route::post('/{id}', [GrnController::class, 'updateStatus'])->name('updateStatus');
    });

    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/store', [CategoryController::class, 'store'])->name('store');
        Route::put('/update/{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('consumption')->name('consumption.')->group(function () {
        Route::get('/', [ConsumptionController::class, 'index'])->name('index');
        Route::get('/view', [ConsumptionController::class, 'index'])->name('view');
        Route::post('/store/{rs}', [ConsumptionController::class, 'store'])->name('store');
        Route::put('/update/{id}', [ConsumptionController::class, 'update'])->name('update');
        Route::get('/create/{id}', [ConsumptionController::class, 'create'])->name('create');
        Route::get('list', [ConsumptionController::class, 'list'])->name('list');
        Route::post('/send-to-hod/{id}', [ConsumptionController::class, 'sendToHod'])->name('sendToHod');
    });

    // Safety and Chatbot
    Route::get('/safety', [RsRequestSlipController::class, 'safetyIndex'])->name('request-slip.safety.index');
    Route::get('/safety-create', [RsRequestSlipController::class, 'safetyCreate'])->name('request-slip.safety.create');
    Route::post('/safety-store', [RsRequestSlipController::class, 'safetyStore'])->name('request-slip.safety.store');
    Route::get('/safety/{id}', [RsRequestSlipController::class, 'safetyShow'])->name('request-slip.safety.show');
    Route::get('/safety-edit/{id}', [RsRequestSlipController::class, 'safetyEdit'])->name('request-slip.safety.edit');
    Route::put('/safety-update/{id}', [RsRequestSlipController::class, 'safetyUpdate'])->name('request-slip.safety.update');
    Route::delete('/safety-delete/{id}', [RsRequestSlipController::class, 'safetyDestroy'])->name('request-slip.safety.destroy');

    Route::post('/chatbot/message', [ChatbotController::class, 'message'])->name('chatbot.message');
});