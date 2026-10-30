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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')
                ->constrained('orders')
                ->nullOnDelete();
            $table->string('payment_reference');
            $table->string('method');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded']);
            $table->decimal('amount', 8, 2);
            $table->timestampTz('paid_at')->nullable();
            $table->timestamps();

            // Constraints

            $table->unique('order_id', 'payments_order_unique');
        });

        DB::statement('
            ALTER TABLE payments 
            ADD CONSTRAINT payments_status_chk
            CHECK (status IN (
                \'pending\',
                \'paid\',
                \'failed\',
                \'refunded\'
            ));
        ');

        DB::statement('
            ALTER TABLE payments 
            ADD CONSTRAINT payments_amount_chk
            CHECK (amount >= 0);
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE payments DROP CONSTRAINT payments_status_chk;');
        DB::statement('ALTER TABLE payments DROP CONSTRAINT payments_amount_chk;');

        Schema::dropIfExists('payments');
    }
};
