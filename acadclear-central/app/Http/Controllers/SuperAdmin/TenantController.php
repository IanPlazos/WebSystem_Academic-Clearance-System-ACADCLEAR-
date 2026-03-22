<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('domain', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $tenants = $query->with('activeSubscription.plan')->paginate(15);
        
        return view('super-admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        $plans = Plan::all();
        return view('super-admin.tenants.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:tenants',
            'domain' => 'required|string|unique:tenants',
            'database' => 'required|string|unique:tenants',
            'plan_id' => 'required|exists:plans,id',
        ]);

        DB::transaction(function () use ($validated) {
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'domain' => $validated['domain'],
                'database' => $validated['database'],
                'status' => 'active',
                'settings' => ['theme' => 'default'],
            ]);

            $plan = Plan::find($validated['plan_id']);
            
            $tenant->subscriptions()->create([
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'status' => 'active',
                'amount_paid' => $plan->price,
                'payment_method' => 'manual',
            ]);
        });

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant created successfully.');
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['subscriptions.plan']);
        $currentSubscription = $tenant->activeSubscription;
        $subscriptionHistory = $tenant->subscriptions()->with('plan')->latest()->get();
        
        return view('super-admin.tenants.show', compact('tenant', 'currentSubscription', 'subscriptionHistory'));
    }

    public function edit(Tenant $tenant)
    {
        return view('super-admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|unique:tenants,domain,' . $tenant->id,
            'status' => 'required|in:active,suspended,expired',
            'suspension_reason' => 'required_if:status,suspended|nullable|string',
        ]);

        $tenant->update($validated);

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    public function toggleStatus(Tenant $tenant)
    {
        if ($tenant->status === 'active') {
            $tenant->suspend('Manually suspended by super admin');
            $message = 'Tenant suspended successfully.';
        } else {
            $tenant->activate();
            $message = 'Tenant activated successfully.';
        }

        return redirect()->back()->with('success', $message);
    }
}