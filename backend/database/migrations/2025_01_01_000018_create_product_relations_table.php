<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_relations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->uuid('related_product_id');
            $table->enum('relation_kind', ['related', 'upsell', 'cross_sell'])->default('related');
            $table->string('badge_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'related_product_id', 'relation_kind'], 'pr_product_related_kind_unique');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('related_product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_relations');
    }
};
