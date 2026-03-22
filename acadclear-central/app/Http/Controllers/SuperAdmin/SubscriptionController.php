<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['tenant', 'plan']);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('tenant_id') && $request->tenant_id != '') {
            $query->where('tenant_id', $request->tenant_id);
        }

        $subscriptions = $query->latest()->paginate(15);
        $tenants = Tenant::all();
        
        return view('super-admin.subscriptions.index', compact('subscriptions', 'tenants'));
    }

    public function create()
    {
        $tenants = Tenant::where('status', 'active')->get();
        $plans = Plan::all();
        
        return view('super-admin.subscriptions.create', compact('tenants', 'plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'required|exists:plans,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'active';
        $validated['meta'] = json_encode(['notes' => $validated['notes'] ?? '']);

        $subscription = Subscription::create($validated);

        // Update tenant's subscription dates
        $tenant = Tenant::find($validated['tenant_id']);
        $tenant->subscription_start = $validated['starts_at'];
        $tenant->subscription_end = $validated['ends_at'];
        $tenant->save();

        return redirect()->route('super-admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription created successfully.');
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['tenant', 'plan']);
        return view('super-admin.subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        $tenants = Tenant::all();
        $plans = Plan::all();
        
        return view('super-admin.subscriptions.edit', compact('subscription', 'tenants', 'plans'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'ends_at' => 'required|date',
            'status' => 'required|in:active,expired,cancelled',
        ]);

        $subscription->update($validated);

        // If subscription is expired, suspend the tenant
        if ($validated['status'] === 'expired') {
            $subscription->tenant->suspend('Subscription expired');
        }

        return redirect()->route('super-admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription updated successfully.');
    }

    public function renew(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'months' => 'required|integer|min:1|max:12',
        ]);

        $plan = Plan::find($validated['plan_id']);
        
        $newSubscription = $subscription->tenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addMonths($validated['months']),
            'status' => 'active',
            'amount_paid' => $validated['amount_paid'],
            'payment_method' => $validated['payment_method'],
        ]);

        // Mark old subscription as expired
        $subscription->update(['status' => 'expired']);

        // Update tenant status
        $subscription->tenant->update(['status' => 'active']);

        return redirect()->route('super-admin.subscriptions.show', $newSubscription)
            ->with('success', 'Subscription renewed successfully.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()->route('super-admin.subscriptions.index')
            ->with('success', 'Subscription deleted successfully.');
    }

    public function getStats()
    {
        $stats = [
            'total_active' => Subscription::where('status', 'active')->count(),
            'total_expired' => Subscription::where('status', 'expired')->count(),
            'monthly_revenue' => Subscription::whereMonth('created_at', now()->month)->sum('amount_paid'),
            'yearly_revenue' => Subscription::whereYear('created_at', now()->year)->sum('amount_paid'),
            'recent_subscriptions' => Subscription::with(['tenant', 'plan'])
                ->latest()
                ->take(10)
                ->get(),
            'expiring_soon' => Subscription::with(['tenant', 'plan'])
                ->where('status', 'active')
                ->where('ends_at', '<=', now()->addDays(30))
                ->where('ends_at', '>=', now())
                ->get(),
        ];

        return response()->json($stats);
    }
}