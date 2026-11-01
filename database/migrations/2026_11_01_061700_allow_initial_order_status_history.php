<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE order_status_histories ALTER COLUMN previous_status DROP NOT NULL'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('order_status_histories')->whereNull('previous_status')->exists()) {
            throw new RuntimeException(
                'Cannot roll back while initial order status history entries exist.'
            );
        }

        DB::statement(
            'ALTER TABLE order_status_histories ALTER COLUMN previous_status SET NOT NULL'
        );
    }
};
