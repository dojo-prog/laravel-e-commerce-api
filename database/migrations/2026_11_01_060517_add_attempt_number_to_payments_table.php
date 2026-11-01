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
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('attempt_number')->nullable();
        });

        DB::statement('
            WITH ranked_payments AS (
                SELECT
                    id,
                    ROW_NUMBER() OVER (
                        PARTITION BY order_id
                        ORDER BY created_at, id
                    ) AS attempt_number
                FROM payments
            )
            UPDATE payments
            SET attempt_number = ranked_payments.attempt_number
            FROM ranked_payments
            WHERE payments.id = ranked_payments.id
        ');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_order_unique');
            $table->unsignedInteger('attempt_number')->nullable(false)->change();
            $table->unique(
                ['order_id', 'attempt_number'],
                'payments_order_attempt_unique'
            );
        });

        DB::statement('
            ALTER TABLE payments
            ADD CONSTRAINT payments_attempt_number_chk
            CHECK (attempt_number >= 1)
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasMultipleAttempts = DB::table('payments')
            ->whereNotNull('order_id')
            ->groupBy('order_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasMultipleAttempts) {
            throw new RuntimeException(
                'Cannot roll back payment attempts while an order has multiple payments.'
            );
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_order_attempt_unique');
            $table->unique('order_id', 'payments_order_unique');
            $table->dropColumn('attempt_number');
        });
    }
};
