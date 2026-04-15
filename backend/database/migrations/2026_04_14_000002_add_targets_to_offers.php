<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('target_type')->default('all')->after('is_active'); // all | products | categories
        });

        Schema::create('offer_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('offer_id');
            $table->uuid('product_id');
            $table->foreign('offer_id')->references('id')->on('offers')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unique(['offer_id', 'product_id']);
        });

        Schema::create('offer_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('offer_id');
            $table->uuid('category_id');
            $table->foreign('offer_id')->references('id')->on('offers')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->unique(['offer_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_categories');
        Schema::dropIfExists('offer_products');
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('target_type');
        });
    }
};
