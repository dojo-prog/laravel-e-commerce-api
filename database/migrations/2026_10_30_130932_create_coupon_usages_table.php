<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('coupon_id')
                ->constrained('coupons')
                ->nullOnDelete();
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignUuid('order_id')
                ->constrained('orders')
                ->nullOnDelete();
            $table->decimal('discount_amount', 8, 2);
            $table->timestampTz('created_at')->default(now());
        
            // Constraints

            $table->unique(
                ['coupon_id', 'order_id'], 
                'coupon_usages_coupon_order_unique'
            );
        });
        
        DB::statement('
            ALTER TABLE coupon_usages 
            ADD CONSTRAINT coupon_usages_discount_amount_chk
            CHECK (discount_amount >= 0);
        ');
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE coupon_usages DROP CONSTRAINT coupon_usages_discount_amount_chk');

        Schema::dropIfExists('coupon_usages');
    }
};
