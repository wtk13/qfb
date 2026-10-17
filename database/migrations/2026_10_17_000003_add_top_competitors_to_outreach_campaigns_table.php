<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outreach_campaigns', function (Blueprint $table) {
            // Top 3 Google results for "<category> in <city>": [{place_id, name, reviews}]
            $table->json('top_competitors')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('outreach_campaigns', function (Blueprint $table) {
            $table->dropColumn('top_competitors');
        });
    }
};
