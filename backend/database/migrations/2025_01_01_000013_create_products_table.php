<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('category_id')->nullable();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('currency_code')->default('UGX');
            $table->decimal('list_price', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->string('bestseller_label')->nullable();
            $table->string('bestseller_category')->nullable();
            $table->string('bought_past_month_label')->nullable();
            $table->string('shipping_label')->nullable();
            $table->string('in_stock_label')->default('In Stock');
            $table->string('delivery_label')->nullable();
            $table->string('returns_label')->nullable();
            $table->string('payment_label')->default('Secure transaction');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured_home')->default(false);
            $table->integer('home_sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
