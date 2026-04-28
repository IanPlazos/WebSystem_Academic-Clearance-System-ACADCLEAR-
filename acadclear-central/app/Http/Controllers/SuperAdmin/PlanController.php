<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * These 3 slugs are constant system plans seeded automatically via migration.
     * They cannot be deleted — only edited (price/features can be updated).
     */
    const SYSTEM_PLANS = ['basic', 'standard', 'premium', 'enterprise'];

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
        // Prevent creating a duplicate of any system plan
        if (in_array(strtolower($request->input('slug', '')), self::SYSTEM_PLANS)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cannot create a plan with a reserved system slug (basic, standard, premium).');
        }

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
        $plan->loadCount('subscriptions');
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
        // Block deletion of the 3 system plans
        if (in_array($plan->slug, self::SYSTEM_PLANS)) {
            return redirect()->back()
                ->with('error', 'System plans (Basic, Standard, Premium) cannot be deleted.');
        }

        if ($plan->subscriptions()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete plan with active subscriptions.');
        }

        $plan->delete();

        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}