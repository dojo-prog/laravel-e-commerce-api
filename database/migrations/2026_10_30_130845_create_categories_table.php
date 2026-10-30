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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->slug('unique');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('image_public_id')->nullable();
            $table->timestamps();
        });

        DB::statement('
            CREATE UNIQUE INDEX categories_name_lower_idx
            ON categories (LOWER(name));
        ');

        DB::statement('
            CREATE UNIQUE INDEX categories_slug_lower_idx
            ON categories (LOWER(slug));
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS categories_name_lower_idx;');
        DB::statement('DROP INDEX IF EXISTS categories_slug_lower_idx;');

        Schema::dropIfExists('categories');
    }
};
