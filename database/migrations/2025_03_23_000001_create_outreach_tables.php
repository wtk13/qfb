<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_name');
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('rating', 3, 1)->default(0);
            $table->unsignedInteger('reviews')->default(0);
            $table->string('place_id')->unique();
            $table->string('google_maps_url')->nullable();
            $table->string('category')->nullable();
            $table->string('city')->nullable();
            $table->string('email_status')->default('pending'); // pending, verified, invalid, unverified
            $table->string('outreach_status')->default('new'); // new, sent, replied, bounced, unsubscribed, converted
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('email_status');
            $table->index('outreach_status');
            $table->index(['category', 'city']);
        });

        Schema::create('outreach_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('category');
            $table->string('city');
            $table->unsignedInteger('leads_scraped')->default(0);
            $table->unsignedInteger('emails_found')->default(0);
            $table->unsignedInteger('emails_verified')->default(0);
            $table->unsignedInteger('emails_sent')->default(0);
            $table->unsignedInteger('replies')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->timestamp('scraped_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['category', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_campaigns');
        Schema::dropIfExists('outreach_leads');
    }
};
