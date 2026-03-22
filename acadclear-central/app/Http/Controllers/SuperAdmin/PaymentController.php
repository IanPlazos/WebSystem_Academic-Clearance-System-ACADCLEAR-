<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['tenant', 'subscription']);

        if ($request->has('tenant_id') && $request->tenant_id != '') {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->latest()->paginate(20);
        $tenants = Tenant::all();
        
        $stats = [
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'monthly_revenue' => Payment::where('status', 'completed')
                ->whereMonth('payment_date', now()->month)
                ->sum('amount'),
            'yearly_revenue' => Payment::where('status', 'completed')
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
        ];

        return view('super-admin.payments.index', compact('payments', 'tenants', 'stats'));
    }

    public function create()
    {
        $tenants = Tenant::where('status', 'active')->get();
        return view('super-admin.payments.create', compact('tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'subscription_id' => 'required|exists:subscriptions,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed,refunded',
            'notes' => 'nullable|string',
        ]);

        Payment::create($validated);

        return redirect()->route('super-admin.payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['tenant', 'subscription.plan']);
        return view('super-admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $tenants = Tenant::all();
        return view('super-admin.payments.edit', compact('payment', 'tenants'));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,failed,refunded',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);

        return redirect()->route('super-admin.payments.show', $payment)
            ->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('super-admin.payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    public function getReceipt(Payment $payment)
    {
        return view('super-admin.payments.receipt', compact('payment'));
    }
}