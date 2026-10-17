<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->string('webhook_token', 40)->nullable()->unique()->after('locale');
        });

        DB::table('business_profiles')->whereNull('webhook_token')->pluck('id')->each(
            fn (string $id) => DB::table('business_profiles')->where('id', $id)->update(['webhook_token' => Str::random(40)])
        );
    }

    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn('webhook_token');
        });
    }
};
