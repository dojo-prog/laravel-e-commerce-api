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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cart_id')
                ->constrained('carts')
                ->cascadeOnDelete();
            $table->foreignUuid('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();

            // Constraints

            $table->unique(['cart_id', 'product_id'], 'cart_items_cart_product_unique');
        });

        DB::statement('
            ALTER TABLE cart_items
            ADD CONSTRAINT cart_items_quantity_chk
            CHECK (quantity >= 0);
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE cart_items DROP CONSTRAINT cart_items_quantity_chk;');

        Schema::dropIfExists('cart_items');
    }
};
