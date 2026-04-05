<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantService;

class DetectTenant
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Detect tenant from subdomain
        $tenantSlug = $this->tenantService->getCurrentTenant();
        
        // Store tenant info for this request
        $request->attributes->set('tenant_slug', $tenantSlug);
        
        // Get tenant details from Central App
        $tenantDetails = $this->tenantService->getTenantDetails($tenantSlug);
        $tenantFeatures = $this->tenantService->getTenantFeatures($tenantSlug);
        
        $request->attributes->set('tenant_details', $tenantDetails);
        $request->attributes->set('tenant_features', $tenantFeatures);
        
        // Share with all views
        view()->share('currentTenant', $tenantDetails);
        view()->share('tenantFeatures', $tenantFeatures);
        
        return $next($request);
    }
}