<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_triages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('feedback_id')->unique();
            $table->string('category');
            $table->string('urgency');
            $table->text('suggested_response');
            $table->text('raw_llm_response');
            $table->string('model_used');
            $table->timestamp('triaged_at');
            $table->timestamps();

            $table->foreign('feedback_id')->references('id')->on('feedback')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_triages');
    }
};
