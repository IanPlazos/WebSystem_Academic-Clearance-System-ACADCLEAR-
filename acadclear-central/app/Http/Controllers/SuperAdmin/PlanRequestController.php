<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\UniversityCredentialsMail;
use App\Models\Plan;
use App\Models\PlanRequest;
use App\Models\Tenant;
use App\Services\TenantDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PlanRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $planRequests = PlanRequest::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => PlanRequest::count(),
            'pending' => PlanRequest::where('status', 'pending')->count(),
            'approved' => PlanRequest::where('status', 'approved')->count(),
            'rejected' => PlanRequest::where('status', 'rejected')->count(),
        ];

        return view('super-admin.plan-requests.index', compact('planRequests', 'counts', 'status'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_slug' => ['nullable', 'string', 'max:255'],
            'tenant_name' => ['nullable', 'string', 'max:255'],
            'plan_name' => ['required', 'string', 'in:Basic,Standard,Enterprise'],
            'institution_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'contact_number' => ['required', 'string', 'max:40'],
            'payment_method' => ['required', 'in:gcash,bank'],
            'amount' => ['required', 'string', 'max:50'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'gcash_number' => ['nullable', 'string', 'max:40', 'required_if:payment_method,gcash'],
            'bank_name' => ['nullable', 'string', 'max:120', 'required_if:payment_method,bank'],
            'bank_account_name' => ['nullable', 'string', 'max:120', 'required_if:payment_method,bank'],
            'bank_account_number' => ['nullable', 'string', 'max:80', 'required_if:payment_method,bank'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $planRequest = PlanRequest::create([
            'tenant_slug' => $validated['tenant_slug'] ?? null,
            'tenant_name' => $validated['tenant_name'] ?? null,
            'plan_name' => $validated['plan_name'],
            'institution_name' => $validated['institution_name'],
            'contact_person' => $validated['contact_person'],
            'email' => $validated['email'],
            'contact_number' => $validated['contact_number'],
            'payment_method' => $validated['payment_method'],
            'amount' => $validated['amount'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'gcash_number' => $validated['gcash_number'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_name' => $validated['bank_account_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan request received successfully.',
            'data' => [
                'id' => $planRequest->id,
                'status' => $planRequest->status,
            ],
        ], 201);
    }

    public function approve(PlanRequest $planRequest)
    {
        $validated = request()->validate([
            'domain' => ['required', 'string', 'max:255', 'unique:tenants,domain'],
        ]);

        if ($planRequest->status === 'approved') {
            return back()->with('error', 'This request has already been approved.');
        }

        $plan = Plan::where('name', $planRequest->plan_name)->first();

        if (!$plan) {
            return back()->with('error', 'No matching pricing plan found for this request.');
        }

        $domain = $this->normalizeDomain($validated['domain']);
        $slugBase = Str::slug($planRequest->institution_name ?: 'tenant');
        $slug = $this->uniqueSlug($slugBase);
        $database = $this->uniqueDatabaseName($slug);
        $adminEmail = $planRequest->email;
        $adminPassword = Str::random(12);

        try {
            [$tenant, $subscription, $loginUrl] = DB::transaction(function () use (
                $planRequest,
                $plan,
                $slug,
                $domain,
                $database,
                $adminEmail,
                $adminPassword
            ) {
                $tenant = Tenant::create([
                    'name' => $planRequest->institution_name,
                    'slug' => $slug,
                    'domain' => $domain,
                    'database' => $database,
                    'status' => 'active',
                    'settings' => [
                        'theme' => 'default',
                        'admin_email' => $adminEmail,
                    ],
                ]);

                $amountPaid = $this->toNumericAmount($planRequest->amount) ?? (float) $plan->price;

                $subscription = $tenant->subscriptions()->create([
                    'plan_id' => $plan->id,
                    'starts_at' => now(),
                    'ends_at' => now()->addMonth(),
                    'status' => 'active',
                    'amount_paid' => $amountPaid,
                    'payment_method' => $planRequest->payment_method,
                    'transaction_id' => $planRequest->payment_reference,
                    'meta' => [
                        'source' => 'plan_request',
                        'plan_request_id' => $planRequest->id,
                    ],
                ]);

                $tenantDatabaseManager = app(TenantDatabaseManager::class);
                $created = $tenantDatabaseManager->createTenant($tenant, [
                    'email' => $adminEmail,
                    'password' => $adminPassword,
                ]);

                if (! $created) {
                    throw new \RuntimeException('Tenant database setup failed.');
                }

                $loginUrl = app()->environment('local')
                    ? 'http://' . $tenant->slug . '.localhost:8000/login'
                    : 'https://' . $tenant->domain . '/login';

                $planRequest->update(['status' => 'approved']);

                return [$tenant, $subscription, $loginUrl];
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Approval failed: ' . $e->getMessage());
        }

        $mailMessage = 'Request approved and tenant created.';

        try {
            Mail::to($adminEmail)->send(new UniversityCredentialsMail(
                tenantName: $tenant->name,
                adminEmail: $adminEmail,
                adminPassword: $adminPassword,
                planName: $plan->name,
                amountPaid: (float) $subscription->amount_paid,
                startsAt: $subscription->starts_at,
                endsAt: $subscription->ends_at,
                paymentMethod: (string) $subscription->payment_method,
                domain: $tenant->domain,
                loginUrl: $loginUrl
            ));

            $mailMessage .= ' Credentials email sent to ' . $adminEmail . '.';
        } catch (\Throwable $mailError) {
            Log::error('Failed sending plan-request approval credentials email', [
                'plan_request_id' => $planRequest->id,
                'tenant_id' => $tenant->id,
                'email' => $adminEmail,
                'error' => $mailError->getMessage(),
            ]);

            $mailMessage .= ' Tenant is created, but email sending failed.';
        }

        return back()->with('success', $mailMessage);
    }

    public function reject(PlanRequest $planRequest)
    {
        $planRequest->update(['status' => 'rejected']);

        return back()->with('success', 'Plan request rejected successfully.');
    }

    private function normalizeDomain(string $domain): string
    {
        $cleanDomain = trim($domain);
        $cleanDomain = preg_replace('#^https?://#i', '', $cleanDomain);
        $cleanDomain = rtrim((string) $cleanDomain, '/');

        return (string) $cleanDomain;
    }

    private function uniqueSlug(string $baseSlug): string
    {
        $slug = Str::limit($baseSlug, 64, '');
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'tenant';
        }

        $candidate = $slug;
        $counter = 1;

        while (Tenant::where('slug', $candidate)->exists()) {
            $suffix = '-' . $counter;
            $candidate = Str::limit($slug, 64 - strlen($suffix), '') . $suffix;
            $counter++;
        }

        return $candidate;
    }

    private function uniqueDatabaseName(string $slug): string
    {
        $base = 'acadclear_' . str_replace('-', '_', $slug);
        $base = Str::limit($base, 60, '');
        $candidate = $base;
        $counter = 1;

        while (Tenant::where('database', $candidate)->exists()) {
            $suffix = '_' . $counter;
            $candidate = Str::limit($base, 64 - strlen($suffix), '') . $suffix;
            $counter++;
        }

        return $candidate;
    }

    private function toNumericAmount(?string $amount): ?float
    {
        if ($amount === null || trim($amount) === '') {
            return null;
        }

        $normalized = str_replace(',', '', $amount);
        $normalized = preg_replace('/[^0-9.]/', '', (string) $normalized);

        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }
}
