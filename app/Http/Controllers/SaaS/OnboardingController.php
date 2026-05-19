<?php

namespace App\Http\Controllers\SaaS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Tenant;
use App\Models\TenantModule;

class OnboardingController extends Controller
{
    public function create()
    {
        return view('saas.create-company');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required',
            'subdomain' => 'required|unique:domains,domain',
            'modules' => 'required|array'
        ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE TENANT
        |--------------------------------------------------------------------------
        */

        $tenant = Tenant::create([
            'id' => $request->subdomain
        ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE DOMAIN
        |--------------------------------------------------------------------------
        */

        $tenant->domains()->create([
            'domain' => $request->subdomain . '.localhost'
        ]);

        /*
        |--------------------------------------------------------------------------
        | ENABLE MODULES
        |--------------------------------------------------------------------------
        */

        foreach ($request->modules as $module) {

            TenantModule::create([
                'tenant_id' => $tenant->id,
                'module' => $module,
                'enabled' => true
            ]);
        }

        return redirect('http://' . $request->subdomain . '.localhost:8000');
    }
}