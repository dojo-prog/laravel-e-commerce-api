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
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignUuid('product_id')
                ->constrained('products')
                ->nullOnDelete();
            $table->string('product_name');
            $table->string('product_sku');
            $table->decimal('unit_price', 8,2);
            $table->integer('quantity');
            $table->decimal('subtotal', 8, 2);
            $table->timestamps();

            // Constraints

            $table->unique(
                ['order_id', 'product_id'], 
                'order_items_order_product_unique'
            );
        });

        DB::statement('
            ALTER TABLE order_items
            ADD CONSTRAINT order_items_unit_price_chk
            CHECK (unit_price >= 0);
        ');

        DB::statement('
            ALTER TABLE order_items
            ADD CONSTRAINT order_items_quantity_chk
            CHECK (quantity >= 0);
        ');

        DB::statement('
            ALTER TABLE order_items
            ADD CONSTRAINT order_items_subtotal_chk
            CHECK (subtotal >= 0);
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE order_items DROP CONSTRAINT order_items_unit_price_chk');
        DB::statement('ALTER TABLE order_items DROP CONSTRAINT order_items_quantity_chk');
        DB::statement('ALTER TABLE order_items DROP CONSTRAINT order_items_subtotal_chk');

        Schema::dropIfExists('order_items');
    }
};
