<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('map_objects', function (Blueprint $table) {
            $table->id();
            //$table->unsignedBigInteger('user_id')->index();
            $table->foreignId('user_id')->constrained();
            $table->string('reviews_url');
            $table->unsignedBigInteger('business_id')->default(0);
            $table->string('business_title')->default('');
            $table->string('rating', 10)->default('');
            $table->unsignedInteger('ratings_count')->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_objects');
    }
};
