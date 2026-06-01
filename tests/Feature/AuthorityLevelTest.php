<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Modules\Shared\App\Models\Role;
use App\Models\User;
use Modules\HRMS\App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorityLevelTest extends TestCase
{
    use RefreshDatabase;

    private $tenant;
    private $superAdminUser;
    private $adminUser;
    private $hodUser;
    private $supervisorUser;
    private $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Force 'central' connection to use sqlite in testing
        config(['database.connections.central' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]]);

        // Migrate the central sqlite database
        $this->artisan('migrate', ['--database' => 'central']);

        // Create missing tables on BOTH sqlite and central SQLite connections
        foreach (['sqlite', 'central'] as $connection) {
            $pdo = \Illuminate\Support\Facades\DB::connection($connection)->getPdo();
            if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
                $pdo->sqliteCreateFunction('DATE_FORMAT', function ($date, $format) {
                    if (!$date) return null;
                    $time = strtotime($date);
                    $phpFormat = str_replace(
                        ['%b', '%y', '%Y', '%m', '%d'],
                        ['M', 'y', 'Y', 'm', 'd'],
                        $format
                    );
                    return date($phpFormat, $time);
                });
            }

            $schema = \Illuminate\Support\Facades\Schema::connection($connection);

            if (!$schema->hasTable('employees')) {
                $schema->create('employees', function ($table) {
                    $table->id();
                    $table->string('name');
                    $table->string('email')->nullable();
                    $table->string('role')->nullable();
                    $table->string('department')->nullable();
                    $table->string('employee_code')->nullable();
                    $table->string('mobile_number')->nullable();
                    $table->timestamps();
                });
            }

            if (!$schema->hasTable('routes')) {
                $schema->create('routes', function ($table) {
                    $table->id();
                    $table->string('name')->nullable();
                    $table->string('route_name')->nullable();
                    $table->string('method')->nullable();
                    $table->unsignedBigInteger('menu_id')->nullable();
                    $table->integer('is_deleted')->default(0);
                    $table->timestamps();
                });
            }

            if (!$schema->hasTable('menus')) {
                $schema->create('menus', function ($table) {
                    $table->id();
                    $table->string('title')->nullable();
                    $table->string('icon')->nullable();
                    $table->unsignedBigInteger('parent_id')->nullable();
                    $table->unsignedBigInteger('route_id')->nullable();
                    $table->integer('sort_order')->default(0);
                    $table->integer('is_deleted')->default(0);
                    $table->timestamps();
                });
            }

            if (!$schema->hasTable('role_permissions')) {
                $schema->create('role_permissions', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('role_id');
                    $table->unsignedBigInteger('menu_id')->nullable();
                    $table->unsignedBigInteger('route_id')->nullable();
                    $table->integer('is_allowed')->default(0);
                    $table->timestamps();
                });
            }

            if (!$schema->hasTable('user_permissions')) {
                $schema->create('user_permissions', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id');
                    $table->unsignedBigInteger('menu_id')->nullable();
                    $table->unsignedBigInteger('route_id')->nullable();
                    $table->integer('is_allowed')->default(0);
                    $table->timestamps();
                });
            }

            if (!$schema->hasTable('user_work_logs')) {
                $schema->create('user_work_logs', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id');
                    $table->string('date');
                    $table->integer('active_seconds')->default(0);
                    $table->timestamps();
                });
            }

            if (!$schema->hasTable('login_histories')) {
                $schema->create('login_histories', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id');
                    $table->string('ip_address')->nullable();
                    $table->text('user_agent')->nullable();
                    $table->timestamp('login_at')->nullable();
                    $table->timestamp('logout_at')->nullable();
                    $table->timestamps();
                });
            }

            if (!$schema->hasTable('callback_messages')) {
                $schema->create('callback_messages', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('lead_id')->nullable();
                    $table->text('message')->nullable();
                    $table->string('status')->nullable();
                    $table->string('bucket')->nullable();
                    $table->string('lead_engagement_status')->nullable();
                    $table->string('followup_type')->nullable();
                    $table->string('followup_status')->nullable();
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->dateTime('next_followup_date')->nullable();
                    $table->integer('is_done')->default(0);
                    $table->string('call_recording')->nullable();
                    $table->timestamps();
                });
            }

            if (!$schema->hasTable('buckets')) {
                $schema->create('buckets', function ($table) {
                    $table->id();
                    $table->string('name')->nullable();
                    $table->unsignedBigInteger('parent_id')->nullable();
                    $table->integer('is_deleted')->default(0);
                    $table->timestamps();
                });
            }

            if (!$schema->hasTable('leads')) {
                $schema->create('leads', function ($table) {
                    $table->id();
                    $table->string('uid')->nullable();
                    $table->string('tenant_id')->nullable();
                    $table->unsignedBigInteger('lead_owner')->nullable();
                    $table->unsignedBigInteger('lead_bucket_id')->nullable();
                    $table->string('lead_status')->nullable();
                    $table->string('lead_engagement_status')->nullable();
                    $table->dateTime('date')->nullable();
                    $table->string('campaign_name')->nullable();
                    $table->string('applying_country_for_a_visa')->nullable();
                    $table->string('what_course_are_you_planning_to_study')->nullable();
                    $table->boolean('verified_lead')->default(false);
                    $table->string('platform')->nullable();
                    $table->timestamps();
                });
            }
        }

        // 1. Create a tenant and initialize
        $this->tenant = Tenant::create(['id' => 'test-tenant']);
        tenancy()->initialize($this->tenant);

        // Enable CRM module for testing
        \App\Models\TenantModule::create([
            'tenant_id' => 'test-tenant',
            'module' => 'CRM',
            'enabled' => true,
        ]);

        // 2. Clear roles table
        Role::truncate();

        // 3. Create roles with authority levels
        $this->createRole(1, 'Super Admin', 100);
        $this->createRole(2, 'Admin', 90);
        $this->createRole(6, 'HOD', 80);
        $this->createRole(8, 'HR', 75);
        $this->createRole(7, 'Supervisor', 70);
        $this->createRole(10, 'Purchase Admin', 65);
        $this->createRole(5, 'Store Admin', 60);
        $this->createRole(3, 'Account', 55);
        $this->createRole(9, 'Sales', 50);
        $this->createRole(4, 'Purchase', 45);
        $this->createRole(11, 'Employee', 10);

        // 4. Create users for testing
        $this->superAdminUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role_id' => 1,
            'email' => 'superadmin@test.com',
        ]);

        $this->adminUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role_id' => 2,
            'email' => 'admin@test.com',
        ]);

        $this->hodUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role_id' => 6,
            'email' => 'hod@test.com',
        ]);

        $this->supervisorUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role_id' => 7,
            'email' => 'supervisor@test.com',
        ]);

        $this->employeeUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role_id' => 11,
            'email' => 'employee@test.com',
        ]);
    }

    private function createRole($id, $name, $level)
    {
        $role = new Role();
        $role->id = $id;
        $role->name = $name;
        $role->guard_name = 'web';
        $role->authority_level = $level;
        $role->save();
        return $role;
    }

    /**
     * Test Model Helper Methods resolve correct values.
     */
    public function test_authority_service_logic_resolves_correctly(): void
    {
        // Super Admin (100) can manage everyone (Admin, HOD, Supervisor, Employee, even other Super Admins)
        $this->assertTrue($this->superAdminUser->canManageUser($this->adminUser));
        $this->assertTrue($this->superAdminUser->canManageUser($this->superAdminUser));

        // Admin (90) can manage HOD (80)
        $this->assertTrue($this->adminUser->canManageUser($this->hodUser));

        // HOD (80) can manage Supervisor (70)
        $this->assertTrue($this->hodUser->canManageUser($this->supervisorUser));

        // Supervisor (70) cannot manage HOD (80)
        $this->assertFalse($this->supervisorUser->canManageUser($this->hodUser));

        // HOD (80) cannot manage another HOD (80)
        $anotherHOD = User::factory()->create(['tenant_id' => $this->tenant->id, 'role_id' => 6]);
        $this->assertFalse($this->hodUser->canManageUser($anotherHOD));

        // Self-management exception: HOD can manage themselves
        $this->assertTrue($this->hodUser->canManageUser($this->hodUser));
    }

    /**
     * Test UserController scoping.
     */
    public function test_user_controller_scopes_listing_by_authority_level(): void
    {
        // Acting as HOD: should see Supervisor and Employee, but not Admin
        $response = $this->actingAs($this->hodUser)->get(route('users.index'));
        
        $response->assertOk();
        $response->assertSee($this->supervisorUser->email);
        $response->assertSee($this->employeeUser->email);
        $response->assertDontSee($this->adminUser->email);
    }

    /**
     * Test Role privilege escalation prevention on user creation.
     */
    public function test_privilege_escalation_prevention_on_user_creation(): void
    {
        // Acting as HOD (80): trying to create an Admin (90) user should fail validation
        $response = $this->actingAs($this->hodUser)->post(route('users.store'), [
            'name' => 'Escalated User',
            'email' => 'escalated@test.com',
            'role_id' => 2, // Admin
            'country_code' => '+1',
            'contact_no' => '1234567890',
            'city' => 'Test City',
        ]);

        $response->assertSessionHasErrors(['role_id']);
        $this->assertDatabaseMissing('users', ['email' => 'escalated@test.com']);
    }

    /**
     * Test role editing permissions.
     */
    public function test_action_restrictions_block_modifying_higher_roles(): void
    {
        // Acting as HOD (80): trying to edit Admin (90) user's page should return 403
        $response = $this->actingAs($this->hodUser)->get(route('users.edit', $this->adminUser->id));
        $response->assertStatus(403);
    }

    /**
     * Test CRM Dashboard scopes metrics by role authority level.
     */
    public function test_crm_dashboard_scopes_metrics_by_authority_level(): void
    {
        // 1. Create a parent bucket so queries don't throw errors
        $bucket = \Modules\CRM\App\Models\Bucket::create([
            'id' => 1,
            'name' => 'Application',
            'is_deleted' => 0,
        ]);

        // 2. Create leads
        // Lead 1: Owned by HOD
        \Modules\CRM\App\Models\Leads::create([
            'id' => 101,
            'tenant_id' => $this->tenant->id,
            'lead_owner' => $this->hodUser->id,
            'lead_bucket_id' => $bucket->id,
            'date' => now()->toDateString(),
            'platform' => 'website',
            'created_at' => now(),
        ]);

        // Lead 2: Owned by Supervisor (subordinate)
        \Modules\CRM\App\Models\Leads::create([
            'id' => 102,
            'tenant_id' => $this->tenant->id,
            'lead_owner' => $this->supervisorUser->id,
            'lead_bucket_id' => $bucket->id,
            'date' => now()->toDateString(),
            'platform' => 'website',
            'created_at' => now(),
        ]);

        // Lead 3: Owned by Admin (superior)
        \Modules\CRM\App\Models\Leads::create([
            'id' => 103,
            'tenant_id' => $this->tenant->id,
            'lead_owner' => $this->adminUser->id,
            'lead_bucket_id' => $bucket->id,
            'date' => now()->toDateString(),
            'platform' => 'website',
            'created_at' => now(),
        ]);

        // 3. Act as HOD and view dashboard
        $response = $this->actingAs($this->hodUser)->get(route('crm.dashboard'));

        $response->assertOk();
        // Since HOD can only manage themselves and Supervisor: total leads should be 2, not 3
        $response->assertViewHas('totalLeads', 2);
    }

    /**
     * Test Daily Lead Report scopes lead counts by role authority level.
     */
    public function test_daily_lead_report_scopes_counts_by_authority_level(): void
    {
        $bucket = \Modules\CRM\App\Models\Bucket::create([
            'id' => 2,
            'name' => 'Follow Up',
            'is_deleted' => 0,
        ]);

        // Lead 1: Owned by HOD
        \Modules\CRM\App\Models\Leads::create([
            'id' => 201,
            'tenant_id' => $this->tenant->id,
            'lead_owner' => $this->hodUser->id,
            'lead_bucket_id' => $bucket->id,
            'date' => now()->toDateString(),
            'created_at' => now(),
        ]);

        // Lead 2: Owned by Supervisor (subordinate)
        \Modules\CRM\App\Models\Leads::create([
            'id' => 202,
            'tenant_id' => $this->tenant->id,
            'lead_owner' => $this->supervisorUser->id,
            'lead_bucket_id' => $bucket->id,
            'date' => now()->toDateString(),
            'created_at' => now(),
        ]);

        // Lead 3: Owned by Admin (superior)
        \Modules\CRM\App\Models\Leads::create([
            'id' => 203,
            'tenant_id' => $this->tenant->id,
            'lead_owner' => $this->adminUser->id,
            'lead_bucket_id' => $bucket->id,
            'date' => now()->toDateString(),
            'created_at' => now(),
        ]);

        // Act as HOD and view daily report
        $response = $this->actingAs($this->hodUser)->get(route('lead.dailyReport'));

        $response->assertOk();
        
        // Retrieve paginated data from view
        $paginated = $response->viewData('paginated');
        $todayStr = now()->toDateString();
        
        $this->assertNotEmpty($paginated[$todayStr] ?? []);
        // Total leads counted for today should be 2 (HOD + Supervisor)
        $this->assertEquals(2, $paginated[$todayStr]['total_leads']);
    }

    /**
     * Test Councillor Report scopes list by role authority level.
     */
    public function test_councillor_report_scopes_listing_by_authority_level(): void
    {
        $bucket = \Modules\CRM\App\Models\Bucket::create([
            'id' => 3,
            'name' => 'Enrollment',
            'is_deleted' => 0,
        ]);

        // Create leads for all three to ensure they appear in general unique list
        \Modules\CRM\App\Models\Leads::create(['id' => 301, 'tenant_id' => $this->tenant->id, 'lead_owner' => $this->hodUser->id, 'lead_bucket_id' => $bucket->id, 'date' => now()->toDateString()]);
        \Modules\CRM\App\Models\Leads::create(['id' => 302, 'tenant_id' => $this->tenant->id, 'lead_owner' => $this->supervisorUser->id, 'lead_bucket_id' => $bucket->id, 'date' => now()->toDateString()]);
        \Modules\CRM\App\Models\Leads::create(['id' => 303, 'tenant_id' => $this->tenant->id, 'lead_owner' => $this->adminUser->id, 'lead_bucket_id' => $bucket->id, 'date' => now()->toDateString()]);

        // Act as HOD and view councillor report
        $response = $this->actingAs($this->hodUser)->get(route('lead.councillorReport'));

        $response->assertOk();

        // 1. Verify dropdown list in view data only has HOD and Supervisor
        $councillors = $response->viewData('councillors');
        $this->assertArrayHasKey($this->hodUser->id, $councillors);
        $this->assertArrayHasKey($this->supervisorUser->id, $councillors);
        $this->assertArrayNotHasKey($this->adminUser->id, $councillors);

        // 2. Verify paginated listing results in view data only includes HOD and Supervisor
        $data = $response->viewData('data');
        $ownersInList = collect($data->items())->pluck('lead_owner')->all();
        $this->assertContains($this->hodUser->id, $ownersInList);
        $this->assertContains($this->supervisorUser->id, $ownersInList);
        $this->assertNotContains($this->adminUser->id, $ownersInList);
    }

    /**
     * Test getSubordinateUserIds resolves own and lower-level user IDs correctly.
     */
    public function test_user_resolves_subordinate_ids(): void
    {
        // HOD user (level 80) should get own ID, Supervisor (level 70) and Employee (level 10)
        // HOD user should NOT get Admin (level 90) or Super Admin (level 100) or another HOD (level 80)
        $anotherHOD = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role_id' => 6, // HOD
            'is_deleted' => 0,
        ]);

        $subordinates = $this->hodUser->getSubordinateUserIds();

        $this->assertContains($this->hodUser->id, $subordinates);
        $this->assertContains($this->supervisorUser->id, $subordinates);
        $this->assertContains($this->employeeUser->id, $subordinates);
        $this->assertNotContains($this->adminUser->id, $subordinates);
        $this->assertNotContains($this->superAdminUser->id, $subordinates);
        $this->assertNotContains($anotherHOD->id, $subordinates);

        // Super Admin (role_id = 1) should get all users
        $superAdminSubordinates = $this->superAdminUser->getSubordinateUserIds();
        $this->assertContains($this->superAdminUser->id, $superAdminSubordinates);
        $this->assertContains($this->hodUser->id, $superAdminSubordinates);
        $this->assertContains($this->adminUser->id, $superAdminSubordinates);
        $this->assertContains($anotherHOD->id, $superAdminSubordinates);
    }
}
