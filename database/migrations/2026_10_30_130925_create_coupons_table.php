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
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code');
            $table->enum('type', ['fixed', 'percentage']);
            $table->decimal('value', 8, 2);
            $table->decimal('minimum_order_amount', 8, 2)->nullable();
            $table->decimal('maximum_discount', 8, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->date('starts_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default('false');
            $table->timestamps();
        });

        DB::statement('
            CREATE UNIQUE INDEX coupons_code_lower_idx
            ON coupons (LOWER(code));
        ');

        DB::statement('
            ALTER TABLE coupons 
            ADD CONSTRAINT coupons_type_chk
            CHECK (type IN (
                \'fixed\',
                \'percentage\'
            ));
        ');

        DB::statement('
            ALTER TABLE coupons 
            ADD CONSTRAINT coupons_value_chk
            CHECK (
                (type = \'fixed\' AND value >= 0) OR
                (type = \'percentage\' AND value <= 100)
            );
        ');

        DB::statement('
            ALTER TABLE coupons 
            ADD CONSTRAINT coupons_order_amount_chk
            CHECK (order_amount >= 0);
        ');

        DB::statement('
            ALTER TABLE coupons 
            ADD CONSTRAINT coupons_maximum_discount_chk
            CHECK (
                (type = \'fixed\' AND maximum_discount >= 0) OR
                (type = \'percentage\' AND maximum_discount <= 100)
            );
        ');

        DB::statement('
            ALTER TABLE coupons 
            ADD CONSTRAINT coupons_usage_limit_chk
            CHECK (usage_limit > 0);
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE coupons DROP CONSTRAINT IF EXISTS coupons_usage_limit_chk;');
        DB::statement('ALTER TABLE coupons DROP CONSTRAINT IF EXISTS coupons_maximum_discount_chk;');
        DB::statement('ALTER TABLE coupons DROP CONSTRAINT IF EXISTS coupons_order_amount_chk;');
        DB::statement('ALTER TABLE coupons DROP CONSTRAINT IF EXISTS coupons_value_chk;');
        DB::statement('ALTER TABLE coupons DROP CONSTRAINT IF EXISTS coupons_type_chk;');
        DB::statement('DROP INDEX IF EXISTS coupons_code_lower_idx;');

        Schema::dropIfExists('coupons');
    }
};
