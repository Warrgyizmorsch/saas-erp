# CRM Module Migration - Command Reference

## Quick Start Commands

### 1. Run Database Migrations

```bash
php artisan migrate --path=Modules/CRM/database/migrations
```

**Expected Output:** All 48 migrations execute successfully without errors

---

### 2. Seed Default Data

```bash
php artisan db:seed --class="Modules\\CRM\\Database\\Seeders\\DatabaseSeeder"
```

**Expected Output:** Seeders run successfully, default users & roles created

---

### 3. Clear Application Cache

```bash
php artisan cache:clear
php artisan config:cache
php artisan route:cache
```

---

### 4. Verify Routes

```bash
php artisan route:list | grep crm
```

**Expected Output:** 200+ /crm routes displayed

---

## Testing Commands

### Tinker Console Tests

```bash
php artisan tinker

# Test models
>>> Modules\CRM\App\Models\User::count()
>>> Modules\CRM\App\Models\Leads::count()
>>> Modules\CRM\App\Models\Role::count()

# Test relationships
>>> $user = Modules\CRM\App\Models\User::first()
>>> $user->role
>>> $user->leads
>>> $user->permissions

# Test leads
>>> $lead = Modules\CRM\App\Models\Leads::first()
>>> $lead->user
>>> $lead->owner
>>> $lead->bucket
>>> $lead->histories

# Create test lead
>>> $lead = Modules\CRM\App\Models\Leads::create(['uid' => 1, 'lead_owner' => 1])
>>> $lead->id

# Exit
>>> exit
```

---

## Artisan Commands

### Check Module Status

```bash
php artisan module:list
php artisan module:status CRM
```

### Enable/Disable Module

```bash
php artisan module:enable CRM
php artisan module:disable CRM
```

### Check Migrations

```bash
php artisan migrate:status
```

### Reset & Migrate (Clean Slate)

```bash
# WARNING: This deletes all data!
php artisan migrate:reset --path=Modules/CRM/database/migrations
php artisan migrate --path=Modules/CRM/database/migrations
php artisan db:seed --class="Modules\\CRM\\Database\\Seeders\\DatabaseSeeder"
```

---

## Application Testing

### Access Web Routes

```bash
# In browser:
http://demo.localhost:8000/crm
http://demo.localhost:8000/crm/lead
http://demo.localhost:8000/crm/users
http://demo.localhost:8000/crm/roles
http://demo.localhost:8000/crm/crm-blog

# Using curl:
curl -X GET http://demo.localhost:8000/crm/lead
curl -H "Accept: application/json" http://demo.localhost:8000/crm/lead
```

### Test API Routes

```bash
# Get blogs
curl http://demo.localhost:8000/api/v1/blogs

# Get single blog
curl http://demo.localhost:8000/api/v1/blogs/my-blog-slug

# Submit warr lead (POST)
curl -X POST http://demo.localhost:8000/api/v1/warr-leads \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "country": "USA"
  }'

# Get service pages
curl http://demo.localhost:8000/api/v1/warr-service-pages
```

---

## Database Queries

### Connect to Database

```bash
# Using MySQL CLI
mysql -h 127.0.0.1 -u root -p saas_erp_tenant

# Check tables
SHOW TABLES;
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM leads;
SELECT COUNT(*) FROM roles;
```

### Verify Key Data

```sql
-- Check users
SELECT id, name, email, role_id FROM users;

-- Check roles
SELECT id, name FROM roles;

-- Check role permissions
SELECT id, role_id, route_id, menu_id FROM role_permissions;

-- Check leads
SELECT id, lead_owner, lead_status, created_at FROM leads;

-- Check lead history
SELECT id, lead_id, user_id, action, created_at FROM lead_history;

-- Check migrations run
SELECT migration FROM migrations WHERE batch > 0;
```

---

## Development Commands

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/CRMTest.php

# Run with coverage
php artisan test --coverage
```

### Watch for Changes

```bash
# Watch and test on file changes
php artisan test --watch

# Watch asset compilation
npm run dev
```

### Generate Test Data

```bash
# Using factory
php artisan tinker
>>> Modules\CRM\App\Models\User::factory(10)->create()
>>> exit
```

---

## Troubleshooting Commands

### Check for Errors

```bash
# View logs
tail -f storage/logs/laravel.log

# Recent errors
php artisan log:tail

# Check config
php artisan config:show
```

### Debug Database

```bash
php artisan db:seed --class="Modules\\CRM\\Database\\Seeders\\UsersTableSeeder" -v

# Enable query logging in tinker
>>> DB::enableQueryLog()
>>> Modules\CRM\App\Models\User::all()
>>> DB::getQueryLog()
```

### Fix Common Issues

```bash
# Clear view cache
php artisan view:clear

# Rebuild autoloader
composer dump-autoload

# Clear all caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:clear

# Restart queue worker (if needed)
php artisan queue:restart
```

---

## Deployment Commands

### Production Setup

```bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Generate app key (if not set)
php artisan key:generate

# Run migrations
php artisan migrate --path=Modules/CRM/database/migrations

# Seed data
php artisan db:seed --class="Modules\\CRM\\Database\\Seeders\\DatabaseSeeder"

# Build assets
npm run build

# Optimize
php artisan optimize
php artisan config:cache
php artisan route:cache
```

---

## Performance Monitoring

### Check Slow Queries

```bash
# In config/database.php, enable query logging
# Then check recent slow queries
```

### Memory Usage

```bash
php artisan tinker
>>> memory_get_usage() / 1024 / 1024 // MB
>>> memory_get_peak_usage() / 1024 / 1024 // Peak MB
```

### Model Count

```bash
php artisan tinker
>>> count(Modules\CRM\App\Models\Leads::all())
>>> Modules\CRM\App\Models\Leads::count()
>>> Modules\CRM\App\Models\Leads::whereYear('created_at', 2026)->count()
```

---

## Module Specific Commands

### Check Namespace

```bash
# Verify all files use correct namespace
find Modules/CRM -name "*.php" -exec grep -l "namespace App" {} \;
# Should return empty if all fixed
```

### View Module Config

```bash
php artisan tinker
>>> config('crm.config')
>>> config('crm.menu')
>>> exit
```

### List Module Routes

```bash
php artisan route:list --name=crm
```

---

## Multi-Tenant Specific

### Access Tenant Context

```bash
# In tenant route context
php artisan tinker --tenant=demo

# Create tenant
php artisan tinancy:install
```

### Test Tenant Isolation

```bash
# Connect as tenant 1
http://tenant1.localhost:8000/crm/lead

# Connect as tenant 2
http://tenant2.localhost:8000/crm/lead

# Data should be isolated
```

---

## Common Workflows

### Complete Fresh Setup

```bash
# 1. Clear everything
php artisan migrate:reset --path=Modules/CRM/database/migrations

# 2. Rebuild
php artisan migrate --path=Modules/CRM/database/migrations
php artisan db:seed --class="Modules\\CRM\\Database\\Seeders\\DatabaseSeeder"

# 3. Clear caches
php artisan cache:clear
php artisan config:cache

# 4. Verify
php artisan route:list | grep crm
php artisan tinker
>>> Modules\CRM\App\Models\User::count()
```

### Test New Feature

```bash
# 1. Create test file
touch tests/Feature/NewFeatureTest.php

# 2. Write test
# ... add test code ...

# 3. Run test
php artisan test tests/Feature/NewFeatureTest.php

# 4. Debug if needed
php artisan tinker
# ... debug commands ...
```

### Deploy to Production

```bash
# 1. Pull code
git pull origin main

# 2. Install dependencies
composer install --optimize-autoloader --no-dev

# 3. Run migrations
php artisan migrate --path=Modules/CRM/database/migrations --force

# 4. Cache configs
php artisan config:cache
php artisan route:cache

# 5. Restart workers
php artisan queue:restart
```

---

## Help & Documentation

### View Help

```bash
php artisan help migrate
php artisan help db:seed
php artisan help module:list
```

### Laravel Documentation

```
Migrations:  https://laravel.com/docs/migrations
Eloquent:    https://laravel.com/docs/eloquent
Modules:     https://nwidart.com/laravel-modules
Tenancy:     https://tenancyforlaravel.com
```

---

## Quick Validation Script

Save this as `validate_crm.sh`:

```bash
#!/bin/bash

echo "=== CRM Module Validation ==="

# Check migrations
echo "Checking migrations..."
php artisan migrate --path=Modules/CRM/database/migrations

# Check seeders
echo "Checking seeders..."
php artisan db:seed --class="Modules\\CRM\\Database\\Seeders\\DatabaseSeeder"

# Check routes
echo "Checking routes..."
php artisan route:list | grep crm | head -5

# Check models
echo "Checking models..."
php artisan tinker << EOF
\$count = Modules\CRM\App\Models\User::count();
echo "Users: " . \$count . "\n";
\$count = Modules\CRM\App\Models\Leads::count();
echo "Leads: " . \$count . "\n";
exit
EOF

echo "=== Validation Complete ==="
```

Run with:

```bash
chmod +x validate_crm.sh
./validate_crm.sh
```

---

## Support

For issues, check:

1. `/TESTING_GUIDE.md` - Detailed testing instructions
2. `/MIGRATION_ARCHITECTURE.md` - Architecture details
3. Laravel logs: `storage/logs/laravel.log`
4. Database logs: MySQL error log

---

**Reference Created:** May 20, 2026  
**Status:** Ready for Phase 4b Testing
