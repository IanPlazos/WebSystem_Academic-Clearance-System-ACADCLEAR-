<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Basic, Standard, Premium
            $table->string('slug')->unique(); // basic, standard, premium
            $table->decimal('price', 10, 2); // 1500, 3000, 0 for custom
            $table->integer('max_students')->nullable(); // 500, 2000, null for unlimited
            $table->boolean('has_advanced_reports')->default(false);
            $table->boolean('has_multi_campus')->default(false);
            $table->boolean('has_custom_branding')->default(false);
            $table->boolean('has_api_access')->default(false);
            $table->json('features')->nullable();
            $table->timestamps();
        });

        // Seed the 3 constant plans automatically — no manual db:seed required
        $now = now();
        DB::table('plans')->insert([
            [
                'name'                 => 'Basic',
                'slug'                 => 'basic',
                'price'                => 1500.00,
                'max_students'         => 500,
                'has_advanced_reports' => false,
                'has_multi_campus'     => false,
                'has_custom_branding'  => false,
                'has_api_access'       => false,
                'features'             => json_encode([
                    'Up to 500 students',
                    'Standard clearance workflow',
                    'Department approval/rejection',
                    'Basic dashboard overview',
                    'Student progress tracking',
                    'Email notifications',
                    'Basic PDF summary',
                    'Email support',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'                 => 'Standard',
                'slug'                 => 'standard',
                'price'                => 3000.00,
                'max_students'         => 2000,
                'has_advanced_reports' => true,
                'has_multi_campus'     => false,
                'has_custom_branding'  => false,
                'has_api_access'       => true,
                'features'             => json_encode([
                    'All Basic features',
                    'Up to 2,000 students',
                    'Advanced reporting',
                    'Department performance reports',
                    'Pending clearance statistics',
                    'Customizable requirements',
                    'Role-based access',
                    'Export to Excel/PDF',
                    'Priority support',
                    'API access',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'                 => 'Premium',
                'slug'                 => 'premium',
                'price'                => 20000.00,
                'max_students'         => null,
                'has_advanced_reports' => true,
                'has_multi_campus'     => true,
                'has_custom_branding'  => true,
                'has_api_access'       => true,
                'features'             => json_encode([
                    'All Standard features',
                    'Unlimited students',
                    'Multi-campus support',
                    'Full customization',
                    'Custom workflow',
                    'Institution branding',
                    'Dedicated support',
                    'Data backup service',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};