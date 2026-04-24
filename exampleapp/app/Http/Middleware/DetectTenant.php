<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantService;
use Illuminate\Support\Facades\Storage;

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
        $tenantLocalLogoUrl = null;
        $tenantBranding = [];

        $brandingPath = "tenant-branding/{$tenantSlug}.json";
        if (Storage::disk('public')->exists($brandingPath)) {
            $decoded = json_decode(Storage::disk('public')->get($brandingPath), true);
            if (is_array($decoded)) {
                $tenantBranding = $decoded;
            }
        }

        foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $extension) {
            $candidatePath = "tenant-branding/{$tenantSlug}.{$extension}";
            if (Storage::disk('public')->exists($candidatePath)) {
                $tenantLocalLogoUrl = asset('storage/' . $candidatePath);
                break;
            }
        }

        if (!$tenantLocalLogoUrl && !empty($tenantBranding['logo'])) {
            $tenantLocalLogoUrl = asset('storage/' . ltrim($tenantBranding['logo'], '/'));
        }

        $tenantColorScheme = $tenantBranding['color_scheme'] ?? 'ocean';
        $tenantColorScheme = match ($tenantColorScheme) {
            'blue' => 'ocean',
            'emerald' => 'forest',
            'amber' => 'sunset',
            default => $tenantColorScheme,
        };

        view()->share('currentTenant', $tenantDetails);
        view()->share('tenantFeatures', $tenantFeatures);
        view()->share('tenantLocalLogoUrl', $tenantLocalLogoUrl);
        view()->share('tenantBranding', $tenantBranding);
        view()->share('tenantColorScheme', $tenantColorScheme);
        
        return $next($request);
    }
}