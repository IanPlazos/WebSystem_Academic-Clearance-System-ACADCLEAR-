<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantService;

class CheckTenantStatus
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
        // Get current tenant slug
        $tenantSlug = $this->tenantService->getCurrentTenant();
        
        // Skip check for certain routes
        $skipRoutes = [
            'tenant.suspended',
            'tenant.suspended.page',
        ];
        
        if (in_array($request->route()->getName(), $skipRoutes)) {
            return $next($request);
        }
        
        // Check if tenant is active
        if (!$this->tenantService->isTenantActive($tenantSlug)) {
            // Store tenant slug in session for the suspended page
            session(['suspended_tenant' => $tenantSlug]);
            
            // Redirect to suspended page
            return redirect()->route('tenant.suspended')
                ->with('error', 'Your university\'s subscription has expired. Please contact your administrator.');
        }
        
        // Store tenant info in request for later use
        $request->attributes->set('tenant_slug', $tenantSlug);
        $request->attributes->set('tenant_details', $this->tenantService->getTenantDetails($tenantSlug));
        $request->attributes->set('tenant_features', $this->tenantService->getTenantFeatures($tenantSlug));
        
        return $next($request);
    }
}