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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('order_number');
            $table->enum('status', [
                'pending', 
                'confirmed', 
                'processing', 
                'shipped', 
                'delivered', 
                'cancelled'
            ]);
            $table->decimal('subtotal', 8, 2);
            $table->decimal('shipping_fee', 8, 2);
            $table->decimal('discount_amount', 8, 2);
            $table->decimal('total_amount', 8, 2);
            $table->jsonb('shipping_address_snapshot');
            $table->text('notes')->nullable();
            $table->timestampTz('placed_at')->default(now());
            $table->timestamps();
        });
        
        DB::statement('
            CREATE UNIQUE INDEX orders_order_number_lower_idx
            ON orders (LOWER(order_number));
        ');

        DB::statement('
            ALTER TABLE orders 
            ADD CONSTRAINT orders_status_chk
            CHECK (status IN (
                \'pending\',
                \'confirmed\',
                \'processing\',
                \'shipped\',
                \'delivered\',
                \'cancelled\'
            ));
        ');
        
        DB::statement('
            ALTER TABLE orders 
            ADD CONSTRAINT orders_subtotal_chk
            CHECK (subtotal >= 0);
        ');

        DB::statement('
            ALTER TABLE orders 
            ADD CONSTRAINT orders_shipping_fee_chk
            CHECK (shipping_fee >= 0);
        ');

        DB::statement('
            ALTER TABLE orders 
            ADD CONSTRAINT orders_discount_amount_chk
            CHECK (discount_amount >= 0 AND discount_amount <= subtotal);
        ');

        DB::statement('
            ALTER TABLE orders 
            ADD CONSTRAINT orders_total_amount_chk
            CHECK (
                total_amount >= 0 
                AND total_amount <= (subtotal + shipping_fee)
            );
        ');
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS orders_order_number_lower_idx');
        DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_status_chk');
        DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_subtotal_chk');
        DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_shipping_fee_chk');
        DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_discount_amount_chk');
        DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_total_amount_chk');

        Schema::dropIfExists('orders');
    }
};
