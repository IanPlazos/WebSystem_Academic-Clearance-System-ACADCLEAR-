<?php
namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price' => 1500,
                'max_students' => 500,
                'has_advanced_reports' => false,
                'has_multi_campus' => false,
                'has_custom_branding' => false,
                'has_api_access' => false,
                'features' => json_encode([
                    'Up to 500 students',
                    'Standard clearance workflow',
                    'Department approval/rejection',
                    'Basic dashboard overview',
                    'Student progress tracking',
                    'Email notifications',
                    'Basic PDF summary',
                    'Email support'
                ])
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'price' => 3000,
                'max_students' => 2000,
                'has_advanced_reports' => true,
                'has_multi_campus' => false,
                'has_custom_branding' => false,
                'has_api_access' => true,
                'features' => json_encode([
                    'All Basic features',
                    'Up to 2,000 students',
                    'Advanced reporting',
                    'Department performance reports',
                    'Pending clearance statistics',
                    'Customizable requirements',
                    'Role-based access',
                    'Export to Excel/PDF',
                    'Priority support'
                ])
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price' => 0,
                'max_students' => null,
                'has_advanced_reports' => true,
                'has_multi_campus' => true,
                'has_custom_branding' => true,
                'has_api_access' => true,
                'features' => json_encode([
                    'All Standard features',
                    'Unlimited students',
                    'Multi-campus support',
                    'Full customization',
                    'Custom workflow',
                    'Institution branding',
                    'Dedicated support',
                    'Data backup service'
                ])
            ]
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Plans seeded successfully!');
    }
}