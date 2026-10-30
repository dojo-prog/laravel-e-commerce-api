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
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->enum('previous_status', [
                'pending', 
                'confirmed', 
                'processing', 
                'shipped', 
                'delivered', 
                'cancelled'
            ]);
            $table->enum('new_status', [
                'pending', 
                'confirmed', 
                'processing', 
                'shipped', 
                'delivered', 
                'cancelled'
            ]);
            $table->text('notes')->nullable();
            $table->timestampTz('created_at')->default(now());

            // Constraints
            
            $table->unique(
                ['order_id', 'previous_status', 'new_status'],
                'order_status_histories_order_prev_new_unique'
            );
        });

        DB::statement('
            ALTER TABLE order_status_histories 
            ADD CONSTRAINT order_status_histories_prev_chk
            CHECK (previous_status IN (
                \'pending\',
                \'confirmed\',
                \'processing\',
                \'shipped\',
                \'delivered\',
                \'cancelled\'
            ));
        ');

        DB::statement('
            ALTER TABLE order_status_histories 
            ADD CONSTRAINT order_status_histories_new_chk
            CHECK (new_status IN (
                \'pending\',
                \'confirmed\',
                \'processing\',
                \'shipped\',
                \'delivered\',
                \'cancelled\'
            ));
        ');

        DB::statement('
            ALTER TABLE order_status_histories
            ADD CONSTRAINT order_status_histories_prev_new_chk
            CHECK (previous_status <> new_status);
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE order_status_histories DROP CONSTRAINT order_status_histories_prev_chk');
        DB::statement('ALTER TABLE order_status_histories DROP CONSTRAINT order_status_histories_new_chk');
        DB::statement('ALTER TABLE order_status_histories DROP CONSTRAINT order_status_histories_prev_new_chk');
        
        Schema::dropIfExists('order_status_histories');
    }
};
