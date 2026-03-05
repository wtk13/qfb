<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('business_profile_id');
            $table->uuid('review_request_id')->nullable();
            $table->unsignedTinyInteger('score');
            $table->string('source')->default('email');
            $table->timestamps();

            $table->foreign('business_profile_id')->references('id')->on('business_profiles')->cascadeOnDelete();
            $table->foreign('review_request_id')->references('id')->on('review_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
