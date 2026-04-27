<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LandingController extends Controller
{
    private const PLAN_CATALOG = [
        'Basic' => [
            'price' => '₱1,500.00',
            'students' => 'Up to 500 students',
            'tag' => null,
            'features' => [
                'Standard clearance workflow',
                'Department approval/rejection',
                'Basic dashboard overview',
                'Email notifications',
                'Basic PDF summary',
            ],
        ],
        'Standard' => [
            'price' => '₱3,000.00',
            'students' => 'Up to 2,000 students',
            'tag' => 'Popular',
            'features' => [
                'Advanced reporting',
                'Role-based access',
                'Export to Excel/PDF',
                'Priority support',
                'API access',
            ],
        ],
        'Premium' => [
            'price' => '₱20,000.00',
            'students' => 'Unlimited students',
            'tag' => null,
            'features' => [
                'Multi-campus support',
                'Full customization',
                'Custom workflow',
                'Institution branding',
                'Dedicated support',
            ],
        ],
    ];

    public function index()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $plans = collect(self::PLAN_CATALOG)
            ->map(fn (array $plan, string $name) => [
                'name' => $name,
                'price' => $plan['price'],
                'tag' => $plan['tag'],
                'students' => $plan['students'],
                'features' => $plan['features'],
            ])
            ->values()
            ->all();

        return view('landing', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_name' => ['required', 'string', 'in:Basic,Standard,Premium'],
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

        $selectedPlan = $validated['plan_name'];
        $canonicalAmount = self::PLAN_CATALOG[$selectedPlan]['price'] ?? $validated['amount'];

        $centralUrl = rtrim((string) env('CENTRAL_APP_URL', 'http://localhost:8001'), '/');

        $response = Http::timeout(10)->post($centralUrl . '/api/plan-requests', [
            'tenant_slug' => request()->attributes->get('tenant_slug') ?: request()->getHost(),
            'tenant_name' => data_get(request()->attributes->get('tenant_details'), 'name'),
            'plan_name' => $validated['plan_name'],
            'institution_name' => $validated['institution_name'],
            'contact_person' => $validated['contact_person'],
            'email' => $validated['email'],
            'contact_number' => $validated['contact_number'],
            'payment_method' => $validated['payment_method'],
            'amount' => $canonicalAmount,
            'payment_reference' => $validated['payment_reference'] ?? null,
            'gcash_number' => $validated['gcash_number'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_name' => $validated['bank_account_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! $response->successful()) {
            return back()
                ->withInput()
                ->withErrors([
                    'submission' => 'We could not submit your request to the central app right now. Please try again.',
                ]);
        }

        return back()->with('success', 'Your plan request was submitted. We will contact you shortly.');
    }
}
