# Phase 4b - CRM Module Testing & Verification Guide

## Status: ✅ ALL FILES MIGRATED - READY FOR TESTING

All 167+ files successfully migrated with proper namespacing from wts-backend to Modules/CRM.

---

## Pre-Testing Checklist

- [x] All models (33) migrated with Modules\CRM\App\Models namespace
- [x] All controllers (21) migrated with proper namespace updates
- [x] All migrations (48) copied to CRM module database directory
- [x] All seeders (6) and factories (1) copied with updated model references
- [x] All views (45) copied to CRM resources directory
- [x] Routes (web.php & api.php) configured with all CRM endpoints
- [x] Services, Jobs, Mail, Exports copied and namespaced
- [x] Console Commands (4) copied and namespaced
- [x] HTTP Requests copied and namespaced
- [x] All namespace references verified (no remaining App\ references)

---

## Testing Phase Checklist

### 1. Database Migration Testing

**Command to run:**

```bash
php artisan migrate --path=Modules/CRM/database/migrations
```

**Expected Outcome:**

- All 48 migrations run successfully
- 20+ database tables created in tenant database
- No errors or warnings

**Verification:**

- Check that these tables exist:
    - users, roles, role_permissions, user_permissions
    - leads, lead_history, lead_attributes, lead_sources, lead_questions
    - menus, routes, login_history, sessions, user_work_logs
    - blogs, authors, categories
    - warr_leads, warr_services, warr_cities, warr_countries, warr_service_pages
    - buckets, todos, applied_universities, universities, courses
    - subject_pages, currency_rates

---

### 2. Database Seeding Testing

**Command to run:**

```bash
php artisan db:seed --class="Modules\\CRM\\Database\\Seeders\\DatabaseSeeder"
```

**Expected Outcome:**

- Admin user created (admin@example.com / user@123)
- Default roles (Admin, User) created
- Navigation menus populated
- Routes registered
- No foreign key constraint errors

**Verification:**

- Check users table for admin account
- Check roles table for default roles
- Check role_permissions table for associations

---

### 3. Model Relationship Testing

**Test File Location:** `/tests/Feature/CRMModelTest.php`

**Key Relationships to Verify:**

```php
// User relationships
$user = User::first();
$user->role;              // Should return Role
$user->permissions();     // Should return Collection of UserPermission
$user->leads();           // Should return Collection of Leads

// Lead relationships
$lead = Leads::first();
$lead->user;              // Should return User
$lead->owner;             // Should return User (lead_owner)
$lead->bucket;            // Should return Bucket
$lead->histories();       // Should return Collection of LeadHistory
$lead->attributes();      // Should return Collection of LeadAttribute
$lead->messages();        // Should return Collection of CallBack

// Bucket relationships
$bucket = Bucket::first();
$bucket->parent();        // Should return parent Bucket (if any)
$bucket->children();      // Should return Collection of child Buckets
$bucket->leads();         // Should return Collection of Leads
```

---

### 4. Route Testing

**Web Routes Test** (Requires authenticated tenant context):

```bash
# Test CRM dashboard
GET /crm

# Test lead endpoints
GET /crm/lead
POST /crm/lead/store
GET /crm/lead/edit/{id}
PUT /crm/lead/update/{id}

# Test user endpoints
GET /crm/users
POST /crm/users/store
GET /crm/users/edit/{id}

# Test role endpoints
GET /crm/roles
POST /crm/roles
PUT /crm/roles/{id}
```

**API Routes Test** (No auth required):

```bash
# Public blog API
GET /api/v1/blogs
GET /api/v1/blogs/{slug}

# Warr leads API
POST /api/v1/warr-leads

# Service pages API
GET /api/v1/warr-service-pages
GET /api/v1/warr-service-pages/{slug}
```

---

### 5. Controller Testing

**Core Controllers to Test:**

1. **LeadController**
    - [ ] index() - List leads with filters
    - [ ] create() - Show create form
    - [ ] store() - Save new lead
    - [ ] edit() - Show edit form
    - [ ] update() - Update lead
    - [ ] getLeadsByType() - Filter by type
    - [ ] bulkOwnerUpdate() - Bulk operations
    - [ ] import() - Excel import
    - [ ] downloadSample() - Sample template

2. **UserController**
    - [ ] index() - List users
    - [ ] create() - Create form
    - [ ] store() - Save user
    - [ ] edit() - Edit form
    - [ ] update() - Update user
    - [ ] indexLog() - Session history
    - [ ] forceLogout() - Force logout user

3. **RoleController**
    - [ ] index() - List roles
    - [ ] store() - Create role
    - [ ] update() - Update role

4. **BlogController**
    - [ ] index() - List blogs
    - [ ] create() - Create form
    - [ ] store() - Save blog
    - [ ] edit() - Edit form
    - [ ] update() - Update blog

---

### 6. View Rendering Testing

**Test Key Views:**

```bash
# Navigate to these URLs and verify views render:
/crm                           # Dashboard
/crm/lead                       # Lead listing
/crm/users                      # User listing
/crm/roles                      # Role listing
/crm/crm-blog                   # Blog listing
/crm/warr-leads                 # Warr leads
/crm/warr-service-pages         # Service pages
```

**Common Issues to Check:**

- [ ] Views extend correct layout (should be Shared module layout)
- [ ] Asset paths are correct (CSS/JS loading)
- [ ] Component includes work (no missing partials)
- [ ] No 404 errors on included blade files

---

### 7. Permission System Testing

**Test Role-Based Access:**

```php
// Create test user with different roles
$adminUser = User::where('role_id', 1)->first();
$normalUser = User::where('role_id', 2)->first();

// Test permission middleware
Route::get('/crm/users', [UserController::class, 'index'])
    ->middleware('check.permission');

// Admin should see users list
// Normal user should be restricted (based on role permissions)
```

**Verify:**

- [ ] check.permission middleware working
- [ ] Users can't access restricted routes
- [ ] Role-based filtering working

---

### 8. Multi-Tenant Isolation Testing

**Test Tenant Isolation:**

```bash
# Access from different domain:
# Tenant 1: demo.localhost:8000
# Tenant 2: other.localhost:8000

# Verify:
# - Each tenant has separate data
# - Leads created in tenant 1 don't appear in tenant 2
# - Users isolated per tenant
# - Sessions are tenant-specific
```

---

### 9. Service & Job Testing

**HomePageDataService**

```php
$service = app(HomePageDataService::class);
$data = $service->getData();
// Should return dashboard aggregated data
```

**LeadsImportJob**

```php
// Test Excel import job
$job = new LeadsImportJob($file);
dispatch($job);
// Should process leads async
```

---

### 10. Command Testing

**Run Console Commands:**

```bash
# Fetch currency rates
php artisan app:fetch-currency-rates

# Send daily WhatsApp report
php artisan app:send-daily-whatsapp-report

# Move blog images to storage
php artisan app:move-blog-images-to-storage

# Download university logos
php artisan app:download-university-logos
```

---

## Common Issues & Solutions

### Issue 1: Model Namespace Errors

**Error:** `Class App\Models\Lead not found`
**Solution:** Ensure all model imports use `Modules\CRM\App\Models\`

### Issue 2: Migration Errors

**Error:** `SQLSTATE[HY000]: General error: 2006 MySQL server has gone away`
**Solution:**

- Check MySQL is running
- Check max_allowed_packet setting
- Run migrations one by one if needed

### Issue 3: View Not Found

**Error:** `View [crm::component] not found`
**Solution:**

- Check blade file exists
- Verify view namespace in Module config
- Check for typos in include paths

### Issue 4: Permission Denied

**Error:** `This action is unauthorized`
**Solution:**

- Check role permissions in role_permissions table
- Verify user has role assigned
- Check check.permission middleware

### Issue 5: Route Not Found

**Error:** `404 | The requested route was not found`
**Solution:**

- Ensure CRM module is enabled
- Check module.enabled:CRM middleware
- Verify route syntax in routes/web.php

---

## Testing Commands Checklist

```bash
# Environment check
php artisan --version
php artisan tinker

# Database
php artisan migrate:status
php artisan migrate --path=Modules/CRM/database/migrations

# Seeders
php artisan db:seed --class="Modules\\CRM\\Database\\Seeders\\DatabaseSeeder"

# Module check
php artisan module:list
php artisan module:enable CRM

# Cache clear (if needed)
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# Test run
php artisan test
```

---

## Sign-Off Checklist

Before moving to Phase 5 (Issues Resolution):

- [ ] All 48 migrations completed without errors
- [ ] All database tables created successfully
- [ ] Seeders ran and populated default data
- [ ] Model relationships verified
- [ ] All web routes accessible
- [ ] All API endpoints responding
- [ ] Views rendering without errors
- [ ] Permission system working
- [ ] Multi-tenant isolation verified
- [ ] Services and jobs executing
- [ ] Console commands running
- [ ] No namespace errors in logs

---

## Next Phase (Phase 5)

If all tests pass, proceed to **Phase 5 - Issues Resolution:**

1. Fix any view path issues
2. Update image/file upload paths
3. Configure environment variables
4. Adjust hardcoded paths
5. Test all workflows end-to-end

---

## Support Documentation

- Migration Summary: `/MIGRATION_SUMMARY.md`
- Module Structure: `Modules/CRM/module.json`
- Composer Config: `Modules/CRM/composer.json`
- Routes Config: `Modules/CRM/routes/web.php` & `routes/api.php`

---

**Last Updated:** May 20, 2026  
**Migration Status:** ✅ COMPLETE  
**Testing Status:** ⏳ READY TO START
