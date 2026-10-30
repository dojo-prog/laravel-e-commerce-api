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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')
                ->constrained('categories')
                ->nullOnDelete();
            $table->string('sku');
            $table->text('description');
            $table->decimal('price', 8, 2);
            $table->integer('stock_quantity');
            $table->enum('status', ['draft', 'active', 'inactive'])
                ->default('draft');
            $table->timestamps();
        });

        DB::statement('
            CREATE UNIQUE INDEX products_sku_lower_idx 
            ON products (LOWER(sku));
        ');

        DB::statement('
            ALTER TABLE products
            ADD CONSTRAINT products_price_chk
            CHECK (price >= 0);
        ');

        DB::statement('
            ALTER TABLE products
            ADD CONSTRAINT products_stock_quantity_chk
            CHECK (stock_quantity >= 0);
        ');

        DB::statement('
            ALTER TABLE products
            ADD CONSTRAINT products_status_chk
            CHECK (status IN (
                \'draft\',
                \'active\',
                \'inactive\'
            ));
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS products_sku_lower_idx;');
        DB::statement('ALTER TABLE products DROP CONSTRAINT products_price_chk');
        DB::statement('ALTER TABLE products DROP CONSTRAINT products_stock_quantity_chk');
        DB::statement('ALTER TABLE products DROP CONSTRAINT products_status_chk');

        Schema::dropIfExists('products');
    }
};
