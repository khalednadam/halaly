<?php

namespace App\Providers;

use App\Facades\ModuleDataFacade;
use App\Helpers\ModuleMetaData;
use App\Models\Backend\Language;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('ModuleDataFacade',function(){
            return new ModuleMetaData();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        try {
            $all_language = Language::all();
        }catch (\Exception $e){
            $all_language = null;
        }

        Paginator::useBootstrap();
        if (get_static_option('site_force_ssl_redirection') === 'on'){
            URL::forceScheme('https');
        }
        Paginator::useBootstrap();
        $this->loadViewsFrom(__DIR__.'/../../plugins/PageBuilder/views','pagebuilder');

        // Register Blade directives for role checking
        $this->registerRoleDirectives();
    }

    /**
     * Register custom Blade directives for role-based visibility
     */
    protected function registerRoleDirectives(): void
    {
        // @isVendor - Show content only to vendors
        Blade::if('isVendor', function () {
            return auth()->check() && auth()->user()->isVendor();
        });

        // @isCustomer - Show content only to customers
        Blade::if('isCustomer', function () {
            return auth()->check() && auth()->user()->isCustomer();
        });

        // @userRole - Show content only if user has specific role
        Blade::if('userRole', function ($role) {
            return auth()->check() && auth()->user()->role === $role;
        });

        // @vendorSubcategory - Show content only if vendor has specific subcategory
        Blade::if('vendorSubcategory', function ($subcategory) {
            return auth()->check() && auth()->user()->isVendorSubcategory($subcategory);
        });
    }
}

