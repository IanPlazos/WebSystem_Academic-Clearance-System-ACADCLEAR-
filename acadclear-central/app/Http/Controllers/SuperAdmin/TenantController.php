<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use App\Mail\UniversityCredentialsMail;
use App\Services\TenantDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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
            'slug' => 'required|string|unique:tenants|regex:/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/|max:64',
            'domain' => 'required|string|unique:tenants',
            'database' => 'required|string|unique:tenants',
            'logo' => 'nullable|image|max:2048',
            'plan_id' => 'required|exists:plans,id',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8|max:255',
        ], [
            'slug.regex' => 'The slug must contain only lowercase letters, numbers, and hyphens (must start and end with alphanumeric).',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('tenant-logos', 'public');
        }

        try {
            [$tenant, $plan, $subscription, $loginUrl] = DB::transaction(function () use ($validated, $logoPath) {
                $tenant = Tenant::create([
                    'name' => $validated['name'],
                    'slug' => $validated['slug'],
                    'domain' => $validated['domain'],
                    'database' => $validated['database'],
                    'logo' => $logoPath ?? null,
                    'status' => 'active',
                    'settings' => [
                        'theme' => 'default',
                        'admin_email' => $validated['admin_email'],
                    ],
                ]);

                $plan = Plan::find($validated['plan_id']);
                
                $subscription = $tenant->subscriptions()->create([
                    'plan_id' => $plan->id,
                    'starts_at' => now(),
                    'ends_at' => now()->addMonth(),
                    'status' => 'active',
                    'amount_paid' => $plan->price,
                    'payment_method' => 'manual',
                ]);

                $tenantDatabaseManager = app(TenantDatabaseManager::class);
                $created = $tenantDatabaseManager->createTenant($tenant, [
                    'email' => $validated['admin_email'],
                    'password' => $validated['admin_password'],
                ]);

                if (!$created) {
                    throw new \RuntimeException('Tenant database setup failed.');
                }

                $loginUrl = app()->environment('local')
                    ? 'http://' . $tenant->slug . '.localhost:8000/login'
                    : 'https://' . $tenant->domain . '/login';

                return [$tenant, $plan, $subscription, $loginUrl];
            });
        } catch (\Throwable $e) {
            return back()
                ->withInput($request->except('admin_password'))
                ->with('error', 'University was not created: ' . $e->getMessage());
        }

        $mailMessage = 'University created successfully. Admin login: ' . $validated['admin_email'];

        try {
            Mail::to($validated['admin_email'])->send(new UniversityCredentialsMail(
                tenantName: $tenant->name,
                adminEmail: $validated['admin_email'],
                adminPassword: $validated['admin_password'],
                planName: $plan->name,
                amountPaid: (float) $subscription->amount_paid,
                startsAt: $subscription->starts_at,
                endsAt: $subscription->ends_at,
                paymentMethod: (string) $subscription->payment_method,
                domain: $tenant->domain,
                loginUrl: $loginUrl
            ));

            $mailMessage .= ' Credentials email sent.';
        } catch (\Throwable $mailError) {
            Log::error('University credentials email failed to send.', [
                'tenant_id' => $tenant->id,
                'admin_email' => $validated['admin_email'],
                'error' => $mailError->getMessage(),
            ]);

            $mailMessage .= ' University created, but email was not sent. Check mail configuration.';
        }

        return redirect()->route('super-admin.tenants.index')
            ->with('success', $mailMessage);
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['subscriptions.plan']);
        $currentSubscription = $tenant->activeSubscription()->first();
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
            'logo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,suspended,expired',
            'suspension_reason' => 'required_if:status,suspended|nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            if ($tenant->logo) {
                Storage::disk('public')->delete($tenant->logo);
            }

            $validated['logo'] = $request->file('logo')->store('tenant-logos', 'public');
        }

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

    public function destroy(Tenant $tenant)
    {
        try {
            DB::transaction(function () use ($tenant) {
                // Delete subscriptions first
                $tenant->subscriptions()->delete();
                
                // Delete the tenant
                $tenant->delete();
                
                // Note: Database and data are preserved for records
                // If you want to delete the database, add that logic here
            });

            return redirect()->route('super-admin.tenants.index')
                ->with('success', 'University deleted successfully. Database preserved for records.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete university: ' . $e->getMessage());
        }
    }
}