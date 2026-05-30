<?php

namespace App\Http\Controllers\SaaS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\Package;

class OnboardingController extends Controller
{
    public function create()
    {
        $packages = Package::all();
        return view('saas.create-company', compact('packages'));
    }

    public function store(Request $request)
    {
        // 1. Construct the fully qualified domain for clean database uniqueness validation
        $host = $request->getHost();
        $suffix = filter_var($host, FILTER_VALIDATE_IP) ? '.' . $host . '.nip.io' : '.' . $host;
        $domain = strtolower($request->subdomain) . $suffix;
        $request->merge(['domain' => $domain]);

        // 2. Perform robust validation to prevent database unique constraint crashes
        $request->validate([
            'company_name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:50|regex:/^[a-zA-Z0-9\-]+$/',
            'domain' => 'required|unique:domains,domain',
            'modules' => 'required|array',
            'package_id' => 'required|exists:packages,id',
            'phone' => 'required|string|max:20',
            'industry' => 'required|string|max:100',
            'company_size' => 'required|string|max:50',
            'country' => 'required|string|max:100',
            'tax_id' => 'nullable|string|max:50',
        ], [
            'subdomain.regex' => 'The subdomain must only contain letters, numbers, and dashes.',
            'domain.unique' => 'This company subdomain has already been taken. Please choose another one.'
        ]);

        // 3. Retrieve the selected package details
        $package = Package::findOrFail($request->package_id);

        // 4. Generate a sequential unique Tenant ID (e.g. demo-1, demo-2) to avoid collisions
        $baseId = str()->slug($request->subdomain);
        $tenantId = $baseId . '-1';
        $counter = 2;
        while (Tenant::where('id', $tenantId)->exists()) {
            $tenantId = $baseId . '-' . $counter;
            $counter++;
        }

        // 5. Create the central tenant with all professional ERP metadata
        $tenant = Tenant::create([
            'id' => $tenantId,
            'company_name' => $request->company_name,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'max_users' => $package->max_users,
            'phone' => $request->phone,
            'industry' => $request->industry,
            'company_size' => $request->company_size,
            'country' => $request->country,
            'tax_id' => $request->tax_id,
        ]);

        // 6. Bind the fully-qualified domain to the newly created tenant
        $tenant->domains()->create([
            'domain' => $domain
        ]);

        // 7. Enable selected modules in the tenant_modules catalog
        foreach ($request->modules as $module) {
            TenantModule::create([
                'tenant_id' => $tenant->id,
                'module' => $module,
                'enabled' => true
            ]);
        }

        // Store tenant ID in session for session-based tenancy identification
        session(['tenant_id' => $tenant->id]);

        /* Commented out subdomain redirection for now as requested
        $port = $request->getPort();
        $portSuffix = $port && $port != 80 && $port != 443 ? ':' . $port : '';

        return redirect('http://' . $domain . $portSuffix);
        */

        // Redirect directly to register to allow creating the tenant user
        return redirect()->route('register');
    }
}