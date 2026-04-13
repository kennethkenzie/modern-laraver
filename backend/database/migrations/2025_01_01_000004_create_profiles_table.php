<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->enum('role', ['customer', 'staff', 'admin'])->default('customer');
            $table->string('avatar_url')->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
