<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id();
            $table->enum('plan_type', ['basic', 'premium', 'business'])->unique();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('MYR');
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default prices
        DB::table('plan_prices')->insert([
            [
                'plan_type' => 'basic',
                'price' => 99.00,
                'currency' => 'MYR',
                'description' => 'Basic NFC Card Plan',
                'features' => json_encode([
                    'One NFC card',
                    'Basic profile',
                    'Contact sharing',
                    'Basic analytics',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plan_type' => 'premium',
                'price' => 199.00,
                'currency' => 'MYR',
                'description' => 'Premium NFC Card Plan',
                'features' => json_encode([
                    'One premium NFC card',
                    'Advanced profile customization',
                    'Social media integration',
                    'Advanced analytics',
                    'Priority support',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plan_type' => 'business',
                'price' => 299.00,
                'currency' => 'MYR',
                'description' => 'Business NFC Card Plan',
                'features' => json_encode([
                    'Multiple NFC cards',
                    'Employee management',
                    'Bulk ordering',
                    'Business analytics',
                    'Dedicated support',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_prices');
    }
};
