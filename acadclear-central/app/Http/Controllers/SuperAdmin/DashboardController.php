<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'suspended_tenants' => Tenant::where('status', 'suspended')->count(),
            'expired_tenants' => Tenant::where('status', 'expired')->count(),
            'total_revenue' => Subscription::sum('amount_paid'),
            'monthly_revenue' => Subscription::whereMonth('created_at', now()->month)->sum('amount_paid'),
        ];

        $recent_tenants = Tenant::latest()->take(5)->get();
        
        $expiring_soon = Subscription::with(['tenant', 'plan'])
            ->where('status', 'active')
            ->where('ends_at', '<=', now()->addDays(7))
            ->where('ends_at', '>=', now())
            ->get();

        return view('super-admin.dashboard', compact('stats', 'recent_tenants', 'expiring_soon'));
    }
}