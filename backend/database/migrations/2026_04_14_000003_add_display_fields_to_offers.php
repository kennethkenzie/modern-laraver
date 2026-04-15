<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('headline')->nullable()->after('name');
            $table->text('description')->nullable()->after('headline');
            $table->string('badge_text', 64)->nullable()->after('description');
            $table->string('banner_image')->nullable()->after('badge_text');
            $table->boolean('is_featured')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['headline', 'description', 'badge_text', 'banner_image', 'is_featured']);
        });
    }
};
