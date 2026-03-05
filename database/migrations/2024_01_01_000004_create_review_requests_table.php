<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('business_profile_id');
            $table->string('recipient_email');
            $table->string('status')->default('pending');
            $table->string('token', 64)->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('business_profile_id')->references('id')->on('business_profiles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_requests');
    }
};
