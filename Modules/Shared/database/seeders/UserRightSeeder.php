<?php

namespace Modules\Shared\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Shared\App\Models\Menu;
use Modules\Shared\App\Models\Route;
use Modules\Shared\App\Models\RolePermission;

class UserRightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clear existing user rights data
        Schema::disableForeignKeyConstraints();
        DB::table('user_permissions')->truncate();
        DB::table('role_permissions')->truncate();
        DB::table('menus')->truncate();
        DB::table('routes')->truncate();
        Schema::enableForeignKeyConstraints();

        // 2. Helper variables
        $superAdminRoleId = 1; // Super Admin Role ID is 1

        // Helper function to register a route
        $registerRoute = function ($name, $routeName, $method, $menuId = null) {
            return Route::create([
                'name' => $name,
                'route_name' => $routeName,
                'method' => $method,
                'menu_id' => $menuId,
                'is_deleted' => 0,
            ]);
        };

        // Helper function to authorize role (Super Admin gets all by default)
        $authorizeSuperAdmin = function ($menuId, $routeId = null) use ($superAdminRoleId) {
            RolePermission::create([
                'role_id' => $superAdminRoleId,
                'menu_id' => $menuId,
                'route_id' => $routeId,
                'is_allowed' => 1,
            ]);
        };

        // Helper function to create a parent menu without direct route
        $createParentMenu = function ($title, $icon, $sortOrder, $parentId = null) use ($authorizeSuperAdmin) {
            $menu = Menu::create([
                'title' => $title,
                'icon' => $icon,
                'parent_id' => $parentId,
                'route_id' => null,
                'sort_order' => $sortOrder,
                'is_deleted' => 0,
            ]);
            $authorizeSuperAdmin($menu->id, null);
            return $menu;
        };

        // Helper function to create a menu linked to a route
        $createMenuWithRoute = function ($title, $icon, $sortOrder, $routeName, $method = 'GET', $parentId = null) use ($registerRoute, $authorizeSuperAdmin) {
            $menu = Menu::create([
                'title' => $title,
                'icon' => $icon,
                'parent_id' => $parentId,
                'route_id' => null, // will link after route creation
                'sort_order' => $sortOrder,
                'is_deleted' => 0,
            ]);

            // Create route linked to this menu
            $route = $registerRoute($title, $routeName, $method, $menu->id);

            // Update menu with the route ID
            $menu->update(['route_id' => $route->id]);

            // Authorize Super Admin for both menu and route
            $authorizeSuperAdmin($menu->id, $route->id);

            return [$menu, $route];
        };

        // ==========================================
        // 1. CORE / DASHBOARD
        // ==========================================
        list($dashboardMenu, $dashboardRoute) = $createMenuWithRoute('Dashboard', 'feather-home', 1, 'dashboard');

        // ==========================================
        // 2. USERS
        // ==========================================
        $usersParent = $createParentMenu('Users', 'feather-users', 2);

        list($usersListMenu, $usersListRoute) = $createMenuWithRoute('Users List', null, 1, 'users.index', 'GET', $usersParent->id);
        // Extra routes for Users List
        $registerRoute('Create User', 'users.create', 'GET', $usersListMenu->id);
        $registerRoute('Store User', 'users.store', 'POST', $usersListMenu->id);
        $registerRoute('Edit User', 'users.edit', 'GET', $usersListMenu->id);
        $registerRoute('Update User', 'users.update', 'PUT', $usersListMenu->id);
        $registerRoute('Delete User', 'users.destroy', 'DELETE', $usersListMenu->id);
        $registerRoute('User History', 'users.history', 'GET', $usersListMenu->id);
        $registerRoute('User Lead History', 'users.leadHistory', 'GET', $usersListMenu->id);
        $registerRoute('Update User Status', 'users.userUpdateStatus', 'PATCH', $usersListMenu->id);

        list($loginHistoryMenu, $loginHistoryRoute) = $createMenuWithRoute('Login History & Sessions', null, 2, 'users.session', 'GET', $usersParent->id);
        // Extra routes for Login History
        $registerRoute('Force Logout User', 'users.logout', 'POST', $loginHistoryMenu->id);
        $registerRoute('Save Work Time', 'save.work.time', 'POST', $loginHistoryMenu->id);

        // ==========================================
        // 3. ROLES & RIGHTS
        // ==========================================
        $rolesParent = $createParentMenu('Roles & Rights', 'feather-shield', 3);

        list($routesMgmtMenu, $routesMgmtRoute) = $createMenuWithRoute('Routes Management', null, 1, 'routes.index', 'GET', $rolesParent->id);
        $registerRoute('Create Route', 'routes.create', 'GET', $routesMgmtMenu->id);
        $registerRoute('Store Route', 'routes.store', 'POST', $routesMgmtMenu->id);
        $registerRoute('Edit Route', 'routes.edit', 'GET', $routesMgmtMenu->id);
        $registerRoute('Update Route', 'routes.update', 'PUT', $routesMgmtMenu->id);
        $registerRoute('Delete Route', 'routes.destroy', 'DELETE', $routesMgmtMenu->id);

        list($menusConfigMenu, $menusConfigRoute) = $createMenuWithRoute('Menus Configuration', null, 2, 'menus.index', 'GET', $rolesParent->id);
        $registerRoute('Create Menu', 'menus.create', 'GET', $menusConfigMenu->id);
        $registerRoute('Store Menu', 'menus.store', 'POST', $menusConfigMenu->id);
        $registerRoute('Edit Menu', 'menus.edit', 'GET', $menusConfigMenu->id);
        $registerRoute('Update Menu', 'menus.update', 'PUT', $menusConfigMenu->id);
        $registerRoute('Delete Menu', 'menus.destroy', 'DELETE', $menusConfigMenu->id);

        list($rolesMgmtMenu, $rolesMgmtRoute) = $createMenuWithRoute('Role Management', null, 3, 'roles.index', 'GET', $rolesParent->id);
        $registerRoute('Create Role', 'roles.create', 'GET', $rolesMgmtMenu->id);
        $registerRoute('Store Role', 'roles.store', 'POST', $rolesMgmtMenu->id);
        $registerRoute('Edit Role', 'roles.edit', 'GET', $rolesMgmtMenu->id);
        $registerRoute('Update Role', 'roles.update', 'PUT', $rolesMgmtMenu->id);
        $registerRoute('Delete Role', 'roles.destroy', 'DELETE', $rolesMgmtMenu->id);

        list($rolePermsMenu, $rolePermsRoute) = $createMenuWithRoute('Role Permissions', null, 4, 'role-permissions.index', 'GET', $rolesParent->id);
        $registerRoute('Update Role Permissions', 'role-permissions-id.update', 'POST', $rolePermsMenu->id);

        list($userPermsMenu, $userPermsRoute) = $createMenuWithRoute('User Overrides', null, 5, 'user-permissions.index', 'GET', $rolesParent->id);
        $registerRoute('Update User Permissions', 'user-permissions-id.update', 'POST', $userPermsMenu->id);

        // ==========================================
        // 4. CRM
        // ==========================================
        $crmParent = $createParentMenu('CRM', 'feather-users', 4);

        list($crmDashboardMenu, $crmDashboardRoute) = $createMenuWithRoute('CRM Dashboard', 'feather-home', 1, 'crm.dashboard', 'GET', $crmParent->id);

        // CRM Leads Parent
        $crmLeadsParent = $createParentMenu('Leads', 'feather-users', 2, $crmParent->id);

        list($modernLeadsMenu, $modernLeadsRoute) = $createMenuWithRoute('Modern Leads', 'feather-user', 1, 'modern.leads.index', 'GET', $crmLeadsParent->id);
        $registerRoute('Quick Update Lead', 'lead.updateQuick', 'POST', $modernLeadsMenu->id);
        $registerRoute('Store Todo Lead', 'lead.storeTodo', 'POST', $modernLeadsMenu->id);

        list($allLeadsMenu, $allLeadsRoute) = $createMenuWithRoute('All Leads', 'feather-list', 2, 'lead.index', 'GET', $crmLeadsParent->id);
        $registerRoute('Create Lead', 'lead.create', 'GET', $allLeadsMenu->id);
        $registerRoute('Store Lead', 'lead.store', 'POST', $allLeadsMenu->id);
        $registerRoute('Edit Lead', 'lead.edit', 'GET', $allLeadsMenu->id);
        $registerRoute('Update Lead', 'lead.update', 'PUT', $allLeadsMenu->id);
        $registerRoute('Update Lead Bucket', 'lead.updateBucket', 'PUT', $allLeadsMenu->id);
        $registerRoute('Update Lead Status', 'lead.updateStatus', 'PUT', $allLeadsMenu->id);
        $registerRoute('Lead History', 'lead.history', 'GET', $allLeadsMenu->id);
        $registerRoute('Send Lead Message', 'lead.sendMessage', 'POST', $allLeadsMenu->id);
        $registerRoute('Update Engagement Status', 'lead.updateEngagementStatus', 'PUT', $allLeadsMenu->id);
        $registerRoute('Bulk Lead Owner Update', 'lead.bulkOwnerUpdate', 'POST', $allLeadsMenu->id);
        $registerRoute('Import Leads', 'lead.import', 'POST', $allLeadsMenu->id);
        $registerRoute('Download Sample Lead Excel', 'lead.sample', 'GET', $allLeadsMenu->id);
        $registerRoute('Lead Import Status', 'lead.importStatus', 'GET', $allLeadsMenu->id);
        $registerRoute('Fetch Templates', 'lead.fetchTemplates', 'GET', $allLeadsMenu->id);
        $registerRoute('Send SMS', 'lead.sendSms', 'POST', $allLeadsMenu->id);
        $registerRoute('Bulk Delete Leads', 'leads.bulkDelete', 'POST', $allLeadsMenu->id);
        $registerRoute('Export Leads', 'leads.export', 'GET', $allLeadsMenu->id);
        $registerRoute('Export Leads Route', 'lead.export', 'GET', $allLeadsMenu->id);
        $registerRoute('Search User by Mobile', 'user.search.byMobile', 'GET', $allLeadsMenu->id);

        list($applicationsMenu, $applicationsRoute) = $createMenuWithRoute('Applications', 'feather-file-text', 3, 'lead.application', 'GET', $crmLeadsParent->id);
        list($leadActivityMenu, $leadActivityRoute) = $createMenuWithRoute('Lead Activity', 'feather-activity', 4, 'lead.leadActivity', 'GET', $crmLeadsParent->id);
        list($followUpsMenu, $followUpsRoute) = $createMenuWithRoute('Follow Ups', 'feather-phone-call', 5, 'lead.followUpData', 'GET', $crmLeadsParent->id);
        $registerRoute('Callback Update', 'lead.callbackUpdate', 'POST', $followUpsMenu->id);
        $registerRoute('Callback Done', 'lead.callbackDone', 'POST', $followUpsMenu->id);

        list($dailyReportMenu, $dailyReportRoute) = $createMenuWithRoute('Daily Report', 'feather-bar-chart-2', 6, 'lead.dailyReport', 'GET', $crmLeadsParent->id);
        list($newDailyReportMenu, $newDailyReportRoute) = $createMenuWithRoute('New Daily Report', 'feather-pie-chart', 7, 'lead.newdailyReport', 'GET', $crmLeadsParent->id);
        list($counsellorReportMenu, $counsellorReportRoute) = $createMenuWithRoute('Counsellor Report', 'feather-users', 8, 'lead.councillorReport', 'GET', $crmLeadsParent->id);
        list($campaignPerfMenu, $campaignPerfRoute) = $createMenuWithRoute('Campaign Performance', 'feather-trending-up', 9, 'lead.campaignPerformance', 'GET', $crmLeadsParent->id);
        list($sourcePerfMenu, $sourcePerfRoute) = $createMenuWithRoute('Source Performance', 'feather-layers', 10, 'lead.sourcePerformance', 'GET', $crmLeadsParent->id);

        // CRM Settings Parent
        $crmSettingsParent = $createParentMenu('CRM Settings', 'feather-settings', 3, $crmParent->id);

        list($bucketsMenu, $bucketsRoute) = $createMenuWithRoute('Buckets', 'feather-folder', 1, 'bucket.index', 'GET', $crmSettingsParent->id);
        $registerRoute('Store Bucket', 'bucket.store', 'POST', $bucketsMenu->id);
        $registerRoute('Edit Bucket', 'bucket.edit', 'GET', $bucketsMenu->id);
        $registerRoute('Update Bucket', 'bucket.update', 'PUT', $bucketsMenu->id);
        $registerRoute('Delete Bucket', 'bucket.destroy', 'DELETE', $bucketsMenu->id);

        list($questionsMenu, $questionsRoute) = $createMenuWithRoute('Lead Questions', 'feather-help-circle', 2, 'lead_questions.index', 'GET', $crmSettingsParent->id);
        $registerRoute('Store Lead Question', 'lead_questions.store', 'POST', $questionsMenu->id);
        $registerRoute('Update Lead Question', 'lead_questions.update', 'PUT', $questionsMenu->id);
        $registerRoute('Delete Lead Question', 'lead_questions.destroy', 'DELETE', $questionsMenu->id);
        $registerRoute('Toggle Lead Question', 'lead_questions.toggle', 'PUT', $questionsMenu->id);

        list($sourcesMenu, $sourcesRoute) = $createMenuWithRoute('Lead Sources', 'feather-database', 3, 'lead_sources.index', 'GET', $crmSettingsParent->id);
        $registerRoute('Store Lead Source', 'lead_sources.store', 'POST', $sourcesMenu->id);
        $registerRoute('Update Lead Source', 'lead_sources.update', 'PUT', $sourcesMenu->id);
        $registerRoute('Toggle Lead Source', 'lead_sources.toggle', 'PUT', $sourcesMenu->id);

        list($categoriesMenu, $categoriesRoute) = $createMenuWithRoute('Categories', 'feather-grid', 4, 'category.index', 'GET', $crmSettingsParent->id);
        $registerRoute('Store Category', 'category.store', 'POST', $categoriesMenu->id);
        $registerRoute('Edit Category', 'category.edit', 'GET', $categoriesMenu->id);
        $registerRoute('Update Category', 'category.update', 'PUT', $categoriesMenu->id);
        $registerRoute('Delete Category', 'category.destroy', 'DELETE', $categoriesMenu->id);
        $registerRoute('Recover Category', 'category.recover', 'POST', $categoriesMenu->id);


        // ==========================================
        // 5. HRMS
        // ==========================================
        $hrmsParent = $createParentMenu('HRMS', 'feather-user-check', 5);

        list($hrmsDashboardMenu, $hrmsDashboardRoute) = $createMenuWithRoute('HRMS Dashboard', 'feather-home', 1, '/hrms', 'GET', $hrmsParent->id);

        // HRMS Employees Parent
        $hrmsEmployeesParent = $createParentMenu('Employees', 'feather-users', 2, $hrmsParent->id);
        $createMenuWithRoute('Add Employee', null, 1, '/hrms/employees/create', 'GET', $hrmsEmployeesParent->id);
        $createMenuWithRoute('View List', null, 2, '/hrms/employees', 'GET', $hrmsEmployeesParent->id);

        // HRMS Payroll Parent
        $hrmsPayrollParent = $createParentMenu('Payroll & Attendance', 'feather-file-text', 3, $hrmsParent->id);
        $createMenuWithRoute('Payroll Admin', null, 1, '/hrms/payroll', 'GET', $hrmsPayrollParent->id);
        $createMenuWithRoute('Attendance List', null, 2, '/hrms/payroll/attendance', 'GET', $hrmsPayrollParent->id);

        // HRMS Master Settings Parent
        $hrmsMasterParent = $createParentMenu('Master Settings', 'feather-database', 4, $hrmsParent->id);
        $createMenuWithRoute('Departments', null, 1, '/hrms/master/departments', 'GET', $hrmsMasterParent->id);
        $createMenuWithRoute('Designations', null, 2, '/hrms/master/designations', 'GET', $hrmsMasterParent->id);

        // HRMS Leave Module Parent
        $hrmsLeaveParent = $createParentMenu('Leave Module', 'feather-calendar', 5, $hrmsParent->id);
        $createMenuWithRoute('Holiday List', null, 1, '/hrms/holidays', 'GET', $hrmsLeaveParent->id);
        $createMenuWithRoute('Leave Allotment', null, 2, '/hrms/leave/allotment', 'GET', $hrmsLeaveParent->id);
        $createMenuWithRoute('Leave Applications', null, 3, '/hrms/leave/history', 'GET', $hrmsLeaveParent->id);

        // HRMS Project Module Parent
        $hrmsProjectParent = $createParentMenu('Project Module', 'feather-briefcase', 6, $hrmsParent->id);
        $createMenuWithRoute('Projects', null, 1, '/hrms/projects', 'GET', $hrmsProjectParent->id);
        $createMenuWithRoute('Daily Tasks', null, 2, '/hrms/daily-tasks', 'GET', $hrmsProjectParent->id);

        $createMenuWithRoute('Job Vacancy', 'feather-user-x', 7, '/hrms/job-vacancy', 'GET', $hrmsParent->id);
        $createMenuWithRoute('Celebrations', 'feather-gift', 8, '/hrms/celebrations', 'GET', $hrmsParent->id);


        // ==========================================
        // 6. INVENTORY
        // ==========================================
        $invParent = $createParentMenu('Inventory', 'feather-grid', 6);

        list($invDashboardMenu, $invDashboardRoute) = $createMenuWithRoute('Inventory Dashboard', 'feather-grid', 1, '/inventory', 'GET', $invParent->id);

        // Inventories Parent
        $invListParent = $createParentMenu('Inventories', 'feather-package', 2, $invParent->id);
        $createMenuWithRoute('Units', null, 1, '/inventory/units', 'GET', $invListParent->id);
        $createMenuWithRoute('Machine', null, 2, '/inventory/product', 'GET', $invListParent->id);
        $createMenuWithRoute('Project', null, 3, '/inventory/project', 'GET', $invListParent->id);

        // Request Slip Parent
        $invRSParent = $createParentMenu('Request Slip', 'feather-file-text', 3, $invParent->id);
        $createMenuWithRoute('Create RS', null, 1, '/inventory/request-slip/create', 'GET', $invRSParent->id);
        $createMenuWithRoute('My Rs', null, 2, '/inventory/request-slip', 'GET', $invRSParent->id);
        $createMenuWithRoute('Semi Finish Goods Raw', null, 3, '/inventory/required-vs-available', 'GET', $invRSParent->id);
        $createMenuWithRoute('Approval', null, 4, '/inventory/approval/requisition', 'GET', $invRSParent->id);
        $createMenuWithRoute('Requisitions', null, 5, '/inventory/approval/requisition', 'GET', $invRSParent->id);

        // Employee Module Parent
        $invEmpParent = $createParentMenu('Employee Module', 'feather-users', 4, $invParent->id);
        $createMenuWithRoute('Employee Dashboard', null, 1, '/inventory/employee-dashboard', 'GET', $invEmpParent->id);

        // Issue Slips Parent
        $invIssueParent = $createParentMenu('Issue Slips', 'feather-share-2', 5, $invParent->id);
        $createMenuWithRoute('Create Issue', null, 1, '/inventory/issue/create', 'GET', $invIssueParent->id);
        $createMenuWithRoute('Departments', null, 2, '/inventory/departments', 'GET', $invIssueParent->id);
        $createMenuWithRoute('Suppliers', null, 3, '/inventory/suppliers', 'GET', $invIssueParent->id);
        $createMenuWithRoute('Issued List', null, 4, '/inventory/issue/view-list', 'GET', $invIssueParent->id);
        $createMenuWithRoute('Inventory categories', null, 5, '/inventory/categories', 'GET', $invIssueParent->id);
        $createMenuWithRoute('Opening Stock', null, 6, '/inventory/inventory/opening-stock', 'GET', $invIssueParent->id);

        // Purchase Request Parent
        $invPRParent = $createParentMenu('Purchase Request', 'feather-shopping-cart', 6, $invParent->id);
        $createMenuWithRoute('Add', null, 1, '/inventory/purchase_request', 'GET', $invPRParent->id);
        $createMenuWithRoute('View List', null, 2, '/inventory/purchase_request/list-view', 'GET', $invPRParent->id);

        // Job Card Parent
        $invJCParent = $createParentMenu('Job Card', 'feather-clipboard', 7, $invParent->id);
        $createMenuWithRoute('Create', null, 1, '/inventory/job_card/create', 'GET', $invJCParent->id);
        $createMenuWithRoute('view', null, 2, '/inventory/job_card/view', 'GET', $invJCParent->id);
        $createMenuWithRoute('PR Approval', null, 3, '/inventory/purchase_request/approval-view', 'GET', $invJCParent->id);
        $createMenuWithRoute('Required V/S Available', null, 4, '/inventory/required-vs-available', 'GET', $invJCParent->id);
        $createMenuWithRoute('Categories', null, 5, '/inventory/categories', 'GET', $invJCParent->id);
        $createMenuWithRoute('Job Card Vendors', null, 6, '/inventory/vendor', 'GET', $invJCParent->id);

        // GRN Parent
        $invGRNParent = $createParentMenu('GRN', 'feather-check-square', 8, $invParent->id);
        $createMenuWithRoute('Create GRN', null, 1, '/inventory/grn/create', 'GET', $invGRNParent->id);
        $createMenuWithRoute('GRN List', null, 2, '/inventory/grn/list', 'GET', $invGRNParent->id);

        // Purchase Order Parent
        $invPOParent = $createParentMenu('Purchase Order', 'feather-file-minus', 9, $invParent->id);
        $createMenuWithRoute('Pending PO', null, 1, '/inventory/purchase-order/approval-view', 'GET', $invPOParent->id);
        $createMenuWithRoute('Create PO', null, 2, '/inventory/purchase-order/create', 'GET', $invPOParent->id);
        $createMenuWithRoute('GRN List', null, 3, '/inventory/grn/list', 'GET', $invPOParent->id);
        $createMenuWithRoute('Exceed RS', null, 4, '/inventory/approval/admin', 'GET', $invPOParent->id);
        $createMenuWithRoute('PO Approval', null, 5, '/inventory/purchase-order/approval-view', 'GET', $invPOParent->id);
        $createMenuWithRoute('All PO', null, 6, '/inventory/purchase-order', 'GET', $invPOParent->id);

        // Request Slip Office Parent
        $invRSOfficeParent = $createParentMenu('Request Slip Office', 'feather-briefcase', 10, $invParent->id);
        $createMenuWithRoute('Placement', null, 1, '/inventory/placement', 'GET', $invRSOfficeParent->id);
        $createMenuWithRoute('View All Rs', null, 2, '/inventory/request-slip/view-all', 'GET', $invRSOfficeParent->id);
        $createMenuWithRoute('Current Stock', null, 3, '/inventory/current-stock', 'GET', $invRSOfficeParent->id);
        $createMenuWithRoute('Create Safety RS', null, 4, '/inventory/safety-create', 'GET', $invRSOfficeParent->id);
        $createMenuWithRoute('Create Office RS', null, 5, '/inventory/safety-create', 'GET', $invRSOfficeParent->id);

        $createMenuWithRoute('Employee Dashboard', 'feather-user', 11, '/inventory/employee-dashboard', 'GET', $invParent->id);

        // Consumption Parent
        $invConsParent = $createParentMenu('Consumption', 'feather-activity', 12, $invParent->id);
        $createMenuWithRoute('consumption List', null, 1, '/inventory/consumption/list', 'GET', $invConsParent->id);

        // 3. Mark all independent and extra routes in `role_permissions`
        $allRoutes = Route::where('is_deleted', 0)->get();
        foreach ($allRoutes as $route) {
            RolePermission::firstOrCreate([
                'role_id' => $superAdminRoleId,
                'route_id' => $route->id,
                'menu_id' => $route->menu_id,
            ], [
                'is_allowed' => 1,
            ]);
        }
    }
}
