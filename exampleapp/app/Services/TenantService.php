<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TenantService
{
    /**
     * Central App URL
     */
    protected $centralUrl;

    public function __construct()
    {
        // Get Central App URL from .env file
        $this->centralUrl = env('CENTRAL_APP_URL', 'http://localhost:8001');
    }

    /**
     * Get the current tenant from the request
     */
    public function getCurrentTenant()
    {
        // Check if tenant is already in session
        if (session()->has('tenant_slug')) {
            return session('tenant_slug');
        }

        // Detect from HTTP host
        $host = request()->getHost();
        
        // Remove port if present
        $host = explode(':', $host)[0];
        
        // Check if it's a subdomain (has a dot and not localhost)
        $parts = explode('.', $host);
        
        if (count($parts) >= 2 && $host !== 'localhost' && $host !== '127.0.0.1') {
            // First part is the subdomain/tenant slug
            $slug = $parts[0];
            
            // Store in session for this request
            session(['tenant_slug' => $slug]);
            
            return $slug;
        }

        // Default for local development without subdomain
        return env('DEFAULT_TENANT', 'default');
    }

    /**
     * Check if tenant is active
     */
    public function isTenantActive($slug = null)
    {
        $slug = $slug ?: $this->getCurrentTenant();
        
        // If default tenant, allow access (for development)
        if ($slug === 'default') {
            return true;
        }
        
        // Cache the result for 5 minutes to reduce API calls
        return Cache::remember("tenant_{$slug}_active", 300, function () use ($slug) {
            try {
                $response = Http::timeout(5)->get("{$this->centralUrl}/api/tenants/{$slug}/status");
                
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['active'] ?? false;
                }
                
                // If central app is not reachable, allow access (fail open)
                return true;
            } catch (\Exception $e) {
                // Log error but allow access to prevent system outage
                \Log::error("Tenant verification failed: " . $e->getMessage());
                return true;
            }
        });
    }

    /**
     * Get tenant details from Central App
     */
    public function getTenantDetails($slug = null)
    {
        $slug = $slug ?: $this->getCurrentTenant();
        
        // If default tenant, return mock data
        if ($slug === 'default') {
            return [
                'id' => 0,
                'name' => 'Default University',
                'slug' => 'default',
                'domain' => 'localhost',
                'database' => env('DB_DATABASE', 'finalwebsys'),
                'status' => 'active',
                'is_active' => true,
                'plan' => [
                    'name' => 'Development',
                    'slug' => 'development',
                    'price' => 0,
                    'max_students' => 9999,
                ],
                'subscription' => [
                    'starts_at' => now()->format('Y-m-d'),
                    'ends_at' => now()->addYear()->format('Y-m-d'),
                    'status' => 'active',
                ],
            ];
        }
        
        return Cache::remember("tenant_{$slug}_details", 3600, function () use ($slug) {
            try {
                $response = Http::timeout(5)->get("{$this->centralUrl}/api/tenants/{$slug}/details");
                
                if ($response->successful()) {
                    return $response->json();
                }
                
                return null;
            } catch (\Exception $e) {
                \Log::error("Failed to get tenant details: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Get tenant's plan features
     */
    public function getTenantFeatures($slug = null)
    {
        $slug = $slug ?: $this->getCurrentTenant();
        
        // If default tenant, return default features
        if ($slug === 'default') {
            return [
                'has_advanced_reports' => true,
                'has_multi_campus' => false,
                'has_custom_branding' => false,
                'has_api_access' => false,
                'max_students' => 9999,
                'features' => ['Development mode - all features enabled'],
                'plan_name' => 'Development',
                'plan_slug' => 'development',
            ];
        }
        
        return Cache::remember("tenant_{$slug}_features", 3600, function () use ($slug) {
            try {
                $response = Http::timeout(5)->get("{$this->centralUrl}/api/tenants/{$slug}/features");
                
                if ($response->successful()) {
                    return $response->json();
                }
                
                return [
                    'has_advanced_reports' => false,
                    'has_multi_campus' => false,
                    'has_custom_branding' => false,
                    'has_api_access' => false,
                    'max_students' => 0,
                    'features' => []
                ];
            } catch (\Exception $e) {
                \Log::error("Failed to get tenant features: " . $e->getMessage());
                return [
                    'has_advanced_reports' => false,
                    'has_multi_campus' => false,
                    'has_custom_branding' => false,
                    'has_api_access' => false,
                    'max_students' => 0,
                    'features' => []
                ];
            }
        });
    }

    /**
     * Get tenant's database name
     */
    public function getTenantDatabase($slug = null)
    {
        $slug = $slug ?: $this->getCurrentTenant();
        
        if ($slug === 'default') {
            return env('DB_DATABASE', 'finalwebsys');
        }
        
        $details = $this->getTenantDetails($slug);
        return $details['database'] ?? null;
    }

    /**
     * Check if tenant database exists
     */
    public function tenantDatabaseExists($slug = null)
    {
        $slug = $slug ?: $this->getCurrentTenant();
        
        if ($slug === 'default') {
            return true;
        }
        
        $database = $this->getTenantDatabase($slug);
        
        if (!$database) {
            return false;
        }
        
        try {
            $result = \DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$database]);
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get tenant's student limit
     */
    public function getStudentLimit($slug = null)
    {
        $slug = $slug ?: $this->getCurrentTenant();
        
        if ($slug === 'default') {
            return 9999;
        }
        
        $features = $this->getTenantFeatures($slug);
        return array_key_exists('max_students', $features) ? $features['max_students'] : 0;
    }

    /**
     * Check if tenant can add more students
     */
    public function canAddMoreStudents($currentCount, $slug = null)
    {
        $limit = $this->getStudentLimit($slug);

        // Null limit means unlimited students (Premium plan).
        if ($limit === null) {
            return true;
        }

        if (!is_numeric($limit)) {
            return false;
        }

        $normalizedLimit = (int) $limit;
        
        if ($normalizedLimit <= 0) {
            return false;
        }
        
        return (int) $currentCount < $normalizedLimit;
    }

    /**
     * Check if tenant can access a specific feature
     */
    public function canAccessFeature($feature, $slug = null)
    {
        $features = $this->getTenantFeatures($slug);
        return $features[$feature] ?? false;
    }

    /**
     * Fetch support chat messages from Central App.
     */
    public function getSupportChatMessages(string $tenantSlug): array
    {
        try {
            $response = Http::withHeaders($this->supportChatHeaders())
                ->timeout(10)
                ->get(rtrim($this->centralUrl, '/') . '/api/support-chat/' . $tenantSlug . '/messages');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'conversation' => null,
                'messages' => [],
            ];
        } catch (\Throwable $e) {
            \Log::error('Support chat fetch failed: ' . $e->getMessage());

            return [
                'conversation' => null,
                'messages' => [],
            ];
        }
    }

    /**
     * Send support chat message to Central App.
     */
    public function sendSupportChatMessage(string $tenantSlug, string $message, ?string $senderName = null, ?int $senderUserId = null): array
    {
        try {
            $response = Http::withHeaders($this->supportChatHeaders())
                ->timeout(10)
                ->post(rtrim($this->centralUrl, '/') . '/api/support-chat/' . $tenantSlug . '/messages', [
                    'message' => $message,
                    'sender_name' => $senderName,
                    'sender_user_id' => $senderUserId,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'message' => 'Failed to send message to support.',
            ];
        } catch (\Throwable $e) {
            \Log::error('Support chat send failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Support service unavailable right now.',
            ];
        }
    }

    /**
     * Get support chat unread badge and recent inbox messages from Central App.
     */
    public function getSupportChatSummary(string $tenantSlug): array
    {
        try {
            $response = Http::withHeaders($this->supportChatHeaders())
                ->timeout(10)
                ->get(rtrim($this->centralUrl, '/') . '/api/support-chat/' . $tenantSlug . '/summary');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'unread_count' => 0,
                'recent_messages' => [],
            ];
        } catch (\Throwable $e) {
            \Log::error('Support chat summary fetch failed: ' . $e->getMessage());

            return [
                'unread_count' => 0,
                'recent_messages' => [],
            ];
        }
    }

    private function supportChatHeaders(): array
    {
        $token = (string) config('services.support_chat.token', '');
        if ($token === '') {
            return [];
        }

        return [
            'X-Support-Token' => $token,
        ];
    }

    /**
     * Get tenant's subscription end date
     */
    public function getSubscriptionEndDate($slug = null)
    {
        $slug = $slug ?: $this->getCurrentTenant();
        
        if ($slug === 'default') {
            return now()->addYear();
        }
        
        $details = $this->getTenantDetails($slug);
        
        if (isset($details['subscription']['ends_at'])) {
            return \Carbon\Carbon::parse($details['subscription']['ends_at']);
        }
        
        if (isset($details['subscription_ends_at'])) {
            return \Carbon\Carbon::parse($details['subscription_ends_at']);
        }
        
        return null;
    }

    /**
     * Check if subscription is expiring soon (within X days)
     */
    public function isSubscriptionExpiringSoon($days = 30, $slug = null)
    {
        $endDate = $this->getSubscriptionEndDate($slug);
        
        if (!$endDate) {
            return false;
        }
        
        return $endDate->diffInDays(now()) <= $days;
    }

    /**
     * Get tenant's current plan name
     */
    public function getCurrentPlan($slug = null)
    {
        $slug = $slug ?: $this->getCurrentTenant();
        
        if ($slug === 'default') {
            return 'Development';
        }
        
        $details = $this->getTenantDetails($slug);
        
        if (isset($details['plan']['name'])) {
            return $details['plan']['name'];
        }
        
        if (isset($details['plan'])) {
            return $details['plan'];
        }
        
        return 'No Plan';
    }

    /**
     * Clear tenant cache (useful after subscription renewal)
     */
    public function clearCache($slug = null)
    {
        $slug = $slug ?: $this->getCurrentTenant();
        
        if ($slug !== 'default') {
            Cache::forget("tenant_{$slug}_active");
            Cache::forget("tenant_{$slug}_details");
            Cache::forget("tenant_{$slug}_features");
        }
    }

    /**
     * Get all available plan features for display
     */
    public function getPlanFeaturesList($slug = null)
    {
        $features = $this->getTenantFeatures($slug);
        return $features['features'] ?? [];
    }

    /**
     * Get tenant's maximum students limit
     */
    public function getMaxStudents($slug = null)
    {
        return $this->getStudentLimit($slug);
    }

    /**
     * Get tenant's current student count (needs to be implemented based on your database)
     */
    public function getCurrentStudentCount($slug = null)
    {
        $slug = $slug ?: $this->getCurrentTenant();
        
        if ($slug === 'default') {
            return \App\Models\User::where('role', 'student')->count();
        }
        
        // This would need to query the tenant's database
        // For now, return a placeholder
        return 0;
    }

    /**
     * Check if tenant has API access
     */
    public function hasApiAccess($slug = null)
    {
        return $this->canAccessFeature('has_api_access', $slug);
    }

    /**
     * Check if tenant has advanced reports
     */
    public function hasAdvancedReports($slug = null)
    {
        return $this->canAccessFeature('has_advanced_reports', $slug);
    }

    /**
     * Check if tenant has multi-campus support
     */
    public function hasMultiCampus($slug = null)
    {
        return $this->canAccessFeature('has_multi_campus', $slug);
    }

    /**
     * Check if tenant has custom branding
     */
    public function hasCustomBranding($slug = null)
    {
        return $this->canAccessFeature('has_custom_branding', $slug);
    }
}