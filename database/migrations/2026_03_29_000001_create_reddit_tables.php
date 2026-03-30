<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reddit_subreddits', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedTinyInteger('tier');
            $table->unsignedTinyInteger('max_posts_per_week')->default(2);
            $table->unsignedTinyInteger('max_comments_per_week')->default(5);
            $table->json('rules_json')->nullable();
            $table->json('keywords_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('reddit_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reddit_subreddit_id')->constrained('reddit_subreddits')->cascadeOnDelete();
            $table->string('reddit_id')->unique();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('author');
            $table->string('url');
            $table->integer('score')->default(0);
            $table->integer('num_comments')->default(0);
            $table->string('thread_type')->default('general');
            $table->string('status')->default('new');
            $table->timestamp('discovered_at');
            $table->timestamps();

            $table->index(['status', 'discovered_at']);
        });

        Schema::create('reddit_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reddit_thread_id')->nullable()->constrained('reddit_threads')->nullOnDelete();
            $table->foreignId('reddit_subreddit_id')->constrained('reddit_subreddits')->cascadeOnDelete();
            $table->string('type');
            $table->string('content_category');
            $table->string('title')->nullable();
            $table->text('body');
            $table->string('status')->default('pending');
            $table->string('reddit_thing_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->integer('reddit_score')->nullable();
            $table->integer('reddit_num_comments')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('reddit_strategy_reports', function (Blueprint $table) {
            $table->id();
            $table->date('period_start');
            $table->date('period_end');
            $table->json('report_json');
            $table->json('recommendations_json');
            $table->json('content_ratio_json');
            $table->json('top_performing_json');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reddit_strategy_reports');
        Schema::dropIfExists('reddit_drafts');
        Schema::dropIfExists('reddit_threads');
        Schema::dropIfExists('reddit_subreddits');
    }
};
