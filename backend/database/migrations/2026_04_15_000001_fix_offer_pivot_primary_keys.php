<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offer_products', function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn('id');
            $table->primary(['offer_id', 'product_id']);
        });

        Schema::table('offer_categories', function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn('id');
            $table->primary(['offer_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::table('offer_products', function (Blueprint $table) {
            $table->dropPrimary();
            $table->uuid('id')->first();
            $table->primary('id');
        });

        Schema::table('offer_categories', function (Blueprint $table) {
            $table->dropPrimary();
            $table->uuid('id')->first();
            $table->primary('id');
        });
    }
};
