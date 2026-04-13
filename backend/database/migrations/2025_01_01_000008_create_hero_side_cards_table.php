<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_side_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->string('image_url');
            $table->string('href');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_side_cards');
    }
};
