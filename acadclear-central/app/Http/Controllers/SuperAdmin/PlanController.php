<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('subscriptions')->get();
        return view('super-admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('super-admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:plans',
            'price' => 'required|numeric|min:0',
            'max_students' => 'nullable|integer|min:0',
            'has_advanced_reports' => 'boolean',
            'has_multi_campus' => 'boolean',
            'has_custom_branding' => 'boolean',
            'has_api_access' => 'boolean',
            'features' => 'nullable|array',
        ]);

        $validated['features'] = json_encode($validated['features'] ?? []);
        
        // Set default values for boolean fields
        $validated['has_advanced_reports'] = $request->has('has_advanced_reports');
        $validated['has_multi_campus'] = $request->has('has_multi_campus');
        $validated['has_custom_branding'] = $request->has('has_custom_branding');
        $validated['has_api_access'] = $request->has('has_api_access');

        Plan::create($validated);

        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('super-admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'max_students' => 'nullable|integer|min:0',
            'has_advanced_reports' => 'boolean',
            'has_multi_campus' => 'boolean',
            'has_custom_branding' => 'boolean',
            'has_api_access' => 'boolean',
            'features' => 'nullable|array',
        ]);

        $validated['features'] = json_encode($validated['features'] ?? []);
        
        $validated['has_advanced_reports'] = $request->has('has_advanced_reports');
        $validated['has_multi_campus'] = $request->has('has_multi_campus');
        $validated['has_custom_branding'] = $request->has('has_custom_branding');
        $validated['has_api_access'] = $request->has('has_api_access');

        $plan->update($validated);

        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->subscriptions()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete plan with active subscriptions.');
        }

        $plan->delete();

        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}