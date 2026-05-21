<?php

namespace Modules\CRM\App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Blade;

class CRMServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'CRM';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'crm';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        parent::boot();

        // Views
        $this->loadViewsFrom(
            module_path('CRM', 'resources/views'),
            'crm'
        );

        // Blade Components
        Blade::anonymousComponentPath(
            module_path('CRM', 'resources/views/components'),
            'crm'
        );
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        parent::register();
    }
}