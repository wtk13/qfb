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
        Schema::table('outreach_campaigns', function (Blueprint $table) {
            $table->unsignedInteger('new_leads_last_scrape')->nullable()->after('conversions');
        });
    }

    public function down(): void
    {
        Schema::table('outreach_campaigns', function (Blueprint $table) {
            $table->dropColumn('new_leads_last_scrape');
        });
    }
};
