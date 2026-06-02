<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. List of standard isolated tables
        $tables = [
            // Shared tables
            'login_histories',
            'user_work_logs',
            'role_permissions',
            'user_permissions',

            // CRM tables
            'leads',
            'buckets',
            'callback_messages',
            'applied_universities',
            'author',
            'blogs',
            'categories',
            'courses',
            'currency_rates',
            'lead_assign_histories',
            'lead_attributes',
            'lead_histories',
            'lead_import_jobs',
            'lead_questions',
            'lead_sources',
            'subject_pages',
            'todo_tasks',
            'university',
            'university_details',
            'warr_cities',
            'warr_countries',
            'warr_leads',
            'warr_services',
            'warr_service_pages',

            // HRMS tables
            'attendances',
            'daily_tasks',
            'departments',
            'task_status_histories',
            'task_follow_ups',
            'payrolls',
            'projects',
            'leave_applications',
            'leave_allotments',
            'job_applications',
            'holidays',
            'employees',
            'designations',

            // Inventory tables
            'inventory_categories',
            'consumptions',
            'inventory_departments',
            'firms',
            'grns',
            'grn_items',
            'inventories',
            'issue_slips',
            'issue_slip_rows',
            'job_cards',
            'job_card_rows',
            'notifications',
            'placements',
            'po_status_logs',
            'po_transactions',
            'products',
            'product_items',
            'inventory_projects',
            'project_documents',
            'project_item',
            'project_main_stage',
            'project_products',
            'project_stage_timelines',
            'project_sub_stages',
            'purchase_orders',
            'purchase_order_items',
            'purchase_requests',
            'purchase_request_items',
            'requisition_slips',
            'request_slip_histories',
            'requisition_slip_rows',
            'requisition_slip_row_pieces',
            'stages',
            'stage_status',
            'stock_transactions',
            'suppliers',
            'supplier_inventories',
            'units',
            'vendors',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                if (!Schema::hasColumn($table, 'tenant_id')) {
                    Schema::table($table, function (Blueprint $tableSchema) use ($table) {
                        // User table already has tenant_id, skip or handle safely
                        $tableSchema->string('tenant_id')->nullable()->index();
                    });
                }
            }
        }

        // 2. Handle the roles table (specifically JSON type)
        if (Schema::hasTable('roles')) {
            if (!Schema::hasColumn('roles', 'tenant_id')) {
                Schema::table('roles', function (Blueprint $tableSchema) {
                    $tableSchema->json('tenant_id')->nullable();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'login_histories',
            'user_work_logs',
            'role_permissions',
            'user_permissions',
            'leads',
            'buckets',
            'callback_messages',
            'applied_universities',
            'author',
            'blogs',
            'categories',
            'courses',
            'currency_rates',
            'lead_assign_histories',
            'lead_attributes',
            'lead_histories',
            'lead_import_jobs',
            'lead_questions',
            'lead_sources',
            'subject_pages',
            'todo_tasks',
            'university',
            'university_details',
            'warr_cities',
            'warr_countries',
            'warr_leads',
            'warr_services',
            'warr_service_pages',
            'attendances',
            'daily_tasks',
            'departments',
            'task_status_histories',
            'task_follow_ups',
            'payrolls',
            'projects',
            'leave_applications',
            'leave_allotments',
            'job_applications',
            'holidays',
            'employees',
            'designations',
            'inventory_categories',
            'consumptions',
            'inventory_departments',
            'firms',
            'grns',
            'grn_items',
            'inventories',
            'issue_slips',
            'issue_slip_rows',
            'job_cards',
            'job_card_rows',
            'notifications',
            'placements',
            'po_status_logs',
            'po_transactions',
            'products',
            'product_items',
            'inventory_projects',
            'project_documents',
            'project_item',
            'project_main_stage',
            'project_products',
            'project_stage_timelines',
            'project_sub_stages',
            'purchase_orders',
            'purchase_order_items',
            'purchase_requests',
            'purchase_request_items',
            'requisition_slips',
            'request_slip_histories',
            'requisition_slip_rows',
            'requisition_slip_row_pieces',
            'stages',
            'stage_status',
            'stock_transactions',
            'suppliers',
            'supplier_inventories',
            'units',
            'vendors',
            'roles',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $tableSchema) {
                    $tableSchema->dropColumn('tenant_id');
                });
            }
        }
    }
};
