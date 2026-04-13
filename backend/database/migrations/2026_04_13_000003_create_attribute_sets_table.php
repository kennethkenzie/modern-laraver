<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_sets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('input_type')->default('dropdown'); // dropdown|text|color|radio|checkbox
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('attribute_set_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attribute_set_id');
            $table->string('value');
            $table->string('color_hex', 10)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('attribute_set_id')
                  ->references('id')
                  ->on('attribute_sets')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_set_options');
        Schema::dropIfExists('attribute_sets');
    }
};
