# Reddit Marketing Agent Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a semi-autonomous Reddit marketing system that scouts threads, drafts AI responses, and publishes after manual approval -- all managed via an admin dashboard.

**Architecture:** Four scheduled jobs (Scout, Drafter, Publisher, Strategist) orchestrate the pipeline. Domain layer defines entities and value objects with port interfaces. Infrastructure implements Reddit API client, Claude AI drafter, Eloquent models/repos. Admin dashboard at `/admin/reddit` for draft review and subreddit management.

**Tech Stack:** Laravel 12, PHP 8.2+, Reddit OAuth2 API, Claude API (Anthropic SDK), Blade + Alpine.js, Tailwind CSS

**Spec:** `docs/superpowers/specs/2026-03-29-reddit-marketing-agent-design.md`

---

## File Map

### Domain Layer (`src/Domain/Reddit/`)

| File | Responsibility |
|------|---------------|
| `Entity/RedditThread.php` | Thread entity with type, status, subreddit reference |
| `Entity/RedditDraft.php` | Draft entity with content category, status lifecycle |
| `Entity/RedditSubreddit.php` | Subreddit entity with tier, cadence, rules |
| `Entity/RedditStrategyReport.php` | Weekly strategy report entity |
| `ValueObject/ThreadType.php` | Enum: how_to_get_reviews, negative_review_help, etc. |
| `ValueObject/ThreadStatus.php` | Enum: new, drafting, drafted, skipped, stale |
| `ValueObject/DraftStatus.php` | Enum: pending, approved, rejected, published, failed |
| `ValueObject/DraftType.php` | Enum: comment, post |
| `ValueObject/ContentCategory.php` | Enum: value, discussion, brand |
| `ValueObject/Phase.php` | Enum: lurk, comment, full |
| `ValueObject/PhasePolicy.php` | Determines current phase from account age |
| `ValueObject/ContentMixPolicy.php` | Enforces 70/20/10 ratio over 30-day window |
| `ValueObject/SubredditCadencePolicy.php` | Enforces per-tier posting limits |
| `Port/RedditApiInterface.php` | Reddit API operations (search, submit, OAuth) |
| `Port/AiDrafterInterface.php` | AI draft generation |
| `Port/AiStrategistInterface.php` | AI strategy report generation |
| `Port/RedditThreadRepositoryInterface.php` | Thread CRUD + queries |
| `Port/RedditDraftRepositoryInterface.php` | Draft CRUD + queries |
| `Port/RedditSubredditRepositoryInterface.php` | Subreddit CRUD |
| `Port/RedditStrategyReportRepositoryInterface.php` | Report CRUD |

### Infrastructure Layer

| File | Responsibility |
|------|---------------|
| `app/Infrastructure/Persistence/Eloquent/RedditThreadModel.php` | Thread Eloquent model |
| `app/Infrastructure/Persistence/Eloquent/RedditDraftModel.php` | Draft Eloquent model |
| `app/Infrastructure/Persistence/Eloquent/RedditSubredditModel.php` | Subreddit Eloquent model |
| `app/Infrastructure/Persistence/Eloquent/RedditStrategyReportModel.php` | Report Eloquent model |
| `app/Infrastructure/Persistence/Repository/EloquentRedditThreadRepository.php` | Thread repo impl |
| `app/Infrastructure/Persistence/Repository/EloquentRedditDraftRepository.php` | Draft repo impl |
| `app/Infrastructure/Persistence/Repository/EloquentRedditSubredditRepository.php` | Subreddit repo impl |
| `app/Infrastructure/Persistence/Repository/EloquentRedditStrategyReportRepository.php` | Report repo impl |
| `app/Infrastructure/Reddit/RedditApiClient.php` | Reddit OAuth2 + search + submit |
| `app/Infrastructure/Reddit/ClaudeAiDrafter.php` | Claude API draft generation |
| `app/Infrastructure/Reddit/ClaudeAiStrategist.php` | Claude API strategy report generation |

### Application Layer

| File | Responsibility |
|------|---------------|
| `app/Application/Command/Reddit/ScoutThreads.php` | Search Reddit, classify threads, save new ones |
| `app/Application/Command/Reddit/DraftResponses.php` | Generate drafts for unprocessed threads |
| `app/Application/Command/Reddit/PublishApprovedDrafts.php` | Post approved drafts to Reddit |
| `app/Application/Command/Reddit/GenerateStrategyReport.php` | Analyze performance, generate report |

### Console Commands

| File | Responsibility |
|------|---------------|
| `app/Console/Commands/Reddit/ScoutRedditThreads.php` | `reddit:scout` artisan command |
| `app/Console/Commands/Reddit/DraftRedditResponses.php` | `reddit:draft` artisan command |
| `app/Console/Commands/Reddit/PublishRedditDrafts.php` | `reddit:publish` artisan command |
| `app/Console/Commands/Reddit/RunRedditStrategist.php` | `reddit:strategist` artisan command |

### HTTP Layer

| File | Responsibility |
|------|---------------|
| `app/Http/Controllers/Admin/RedditDashboardController.php` | Dashboard overview |
| `app/Http/Controllers/Admin/RedditDraftController.php` | Draft list, show, approve, reject, edit, retry |
| `app/Http/Controllers/Admin/RedditSubredditController.php` | Subreddit CRUD |
| `app/Http/Controllers/Admin/RedditStrategyReportController.php` | View reports |

### Views

| File | Responsibility |
|------|---------------|
| `resources/views/admin/reddit/dashboard.blade.php` | Overview with phase indicator, ratio chart, recent drafts |
| `resources/views/admin/reddit/drafts/index.blade.php` | Pending drafts table with approve/reject/retry actions |
| `resources/views/admin/reddit/drafts/show.blade.php` | Single draft with thread context, edit + approve |
| `resources/views/admin/reddit/subreddits/index.blade.php` | Subreddit management table |
| `resources/views/admin/reddit/reports/show.blade.php` | Strategy report view |

### Database

| File | Responsibility |
|------|---------------|
| `database/migrations/2026_03_29_000001_create_reddit_tables.php` | All 4 tables in one migration |
| `database/seeders/RedditSubredditSeeder.php` | 20+ subreddits from playbook |

### Tests

| File | Responsibility |
|------|---------------|
| `tests/Unit/Domain/Reddit/ThreadTypeTest.php` | ThreadType classification |
| `tests/Unit/Domain/Reddit/PhasePolicyTest.php` | Phase determination from account age |
| `tests/Unit/Domain/Reddit/ContentMixPolicyTest.php` | 70/20/10 ratio enforcement |
| `tests/Unit/Domain/Reddit/SubredditCadencePolicyTest.php` | Per-tier cadence limits |
| `tests/Feature/Reddit/RedditDashboardTest.php` | Admin dashboard access + data display |
| `tests/Feature/Reddit/RedditDraftControllerTest.php` | CRUD, approve, reject, retry flows |
| `tests/Feature/Reddit/ScoutThreadsTest.php` | Scout command with mocked Reddit API |
| `tests/Feature/Reddit/DraftResponsesTest.php` | Drafter command with mocked AI drafter |
| `tests/Feature/Reddit/PublishApprovedDraftsTest.php` | Publisher command with mocked Reddit API |

### Config

| File | Responsibility |
|------|---------------|
| `config/reddit.php` | Reddit config (credentials, models, dry run, enabled) |

---

## Task 1: Database Migration

**Files:**
- Create: `database/migrations/2026_03_29_000001_create_reddit_tables.php`

- [ ] **Step 1: Create migration file**

```php
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
```

- [ ] **Step 2: Run migration**

Run: `./vendor/bin/sail artisan migrate`
Expected: 4 tables created successfully.

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_03_29_000001_create_reddit_tables.php
git commit -m "feat(reddit): add database migration for reddit tables"
```

---

## Task 2: Config File

**Files:**
- Create: `config/reddit.php`

- [ ] **Step 1: Create config file**

```php
<?php

return [
    'client_id' => env('REDDIT_CLIENT_ID'),
    'client_secret' => env('REDDIT_CLIENT_SECRET'),
    'username' => env('REDDIT_USERNAME'),
    'password' => env('REDDIT_PASSWORD'),
    'user_agent' => env('REDDIT_USER_AGENT', 'web:quickfeedback:v1.0'),
    'account_created_at' => env('REDDIT_ACCOUNT_CREATED_AT'),
    'dry_run' => env('REDDIT_DRY_RUN', true),
    'enabled' => env('REDDIT_ENABLED', false),
    'drafter_model' => env('REDDIT_DRAFTER_MODEL', 'claude-haiku-4-5-20251001'),
    'strategist_model' => env('REDDIT_STRATEGIST_MODEL', 'claude-sonnet-4-6'),
    'anthropic_api_key' => env('ANTHROPIC_API_KEY'),
];
```

- [ ] **Step 2: Commit**

```bash
git add config/reddit.php
git commit -m "feat(reddit): add reddit config file"
```

---

## Task 3: Domain Value Objects and Enums

**Files:**
- Create: `src/Domain/Reddit/ValueObject/ThreadType.php`
- Create: `src/Domain/Reddit/ValueObject/ThreadStatus.php`
- Create: `src/Domain/Reddit/ValueObject/DraftStatus.php`
- Create: `src/Domain/Reddit/ValueObject/DraftType.php`
- Create: `src/Domain/Reddit/ValueObject/ContentCategory.php`
- Create: `src/Domain/Reddit/ValueObject/Phase.php`
- Test: `tests/Unit/Domain/Reddit/ThreadTypeTest.php`

- [ ] **Step 1: Write ThreadType enum**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

enum ThreadType: string
{
    case HowToGetReviews = 'how_to_get_reviews';
    case NegativeReviewHelp = 'negative_review_help';
    case StartingBusiness = 'starting_business';
    case ToolRecommendation = 'tool_recommendation';
    case LocalSeo = 'local_seo';
    case General = 'general';

    /**
     * Classify a thread by matching title + body against keyword patterns.
     */
    public static function classify(string $title, ?string $body = null): self
    {
        $text = strtolower($title . ' ' . ($body ?? ''));

        $patterns = [
            self::HowToGetReviews => '/\b(get|more|increase)\b.*\breview/',
            self::NegativeReviewHelp => '/\b(negative|bad|terrible|1.star)\b.*\breview/',
            self::StartingBusiness => '/\b(starting|new|launch)\b.*\bbusiness/',
            self::ToolRecommendation => '/\b(tool|software|recommend|app)\b.*\breview/',
            self::LocalSeo => '/\blocal\s+seo\b|\bgoogle\s+rank/',
        ];

        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $text)) {
                return $type;
            }
        }

        return self::General;
    }
}
```

- [ ] **Step 2: Write ThreadStatus enum**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

enum ThreadStatus: string
{
    case New = 'new';
    case Drafting = 'drafting';
    case Drafted = 'drafted';
    case Skipped = 'skipped';
    case Stale = 'stale';
}
```

- [ ] **Step 3: Write DraftStatus enum**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

enum DraftStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Published = 'published';
    case Failed = 'failed';
}
```

- [ ] **Step 4: Write DraftType enum**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

enum DraftType: string
{
    case Comment = 'comment';
    case Post = 'post';
}
```

- [ ] **Step 5: Write ContentCategory enum**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

enum ContentCategory: string
{
    case Value = 'value';
    case Discussion = 'discussion';
    case Brand = 'brand';
}
```

- [ ] **Step 6: Write Phase enum**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

enum Phase: string
{
    case Lurk = 'lurk';
    case Comment = 'comment';
    case Full = 'full';
}
```

- [ ] **Step 7: Write ThreadType test**

```php
<?php

namespace Tests\Unit\Domain\Reddit;

use Domain\Reddit\ValueObject\ThreadType;
use PHPUnit\Framework\TestCase;

class ThreadTypeTest extends TestCase
{
    public function test_classifies_how_to_get_reviews(): void
    {
        $this->assertSame(ThreadType::HowToGetReviews, ThreadType::classify('How to get more Google reviews?'));
        $this->assertSame(ThreadType::HowToGetReviews, ThreadType::classify('Tips to increase review count'));
    }

    public function test_classifies_negative_review_help(): void
    {
        $this->assertSame(ThreadType::NegativeReviewHelp, ThreadType::classify('How do I handle a bad review?'));
        $this->assertSame(ThreadType::NegativeReviewHelp, ThreadType::classify('Got a 1-star review, help'));
    }

    public function test_classifies_starting_business(): void
    {
        $this->assertSame(ThreadType::StartingBusiness, ThreadType::classify('Starting a new business, any advice?'));
    }

    public function test_classifies_tool_recommendation(): void
    {
        $this->assertSame(ThreadType::ToolRecommendation, ThreadType::classify('Best tool for review management?'));
        $this->assertSame(ThreadType::ToolRecommendation, ThreadType::classify('Software to manage customer reviews'));
    }

    public function test_classifies_local_seo(): void
    {
        $this->assertSame(ThreadType::LocalSeo, ThreadType::classify('How to improve local SEO'));
        $this->assertSame(ThreadType::LocalSeo, ThreadType::classify('Struggling with Google ranking'));
    }

    public function test_defaults_to_general(): void
    {
        $this->assertSame(ThreadType::General, ThreadType::classify('Random discussion topic'));
    }

    public function test_uses_body_for_classification(): void
    {
        $this->assertSame(
            ThreadType::HowToGetReviews,
            ThreadType::classify('Help needed', 'I want to get more reviews for my business')
        );
    }
}
```

- [ ] **Step 8: Run test to verify it passes**

Run: `./vendor/bin/sail artisan test --filter=ThreadTypeTest`
Expected: PASS (7 tests)

- [ ] **Step 9: Commit**

```bash
git add src/Domain/Reddit/ValueObject/ tests/Unit/Domain/Reddit/ThreadTypeTest.php
git commit -m "feat(reddit): add domain value objects and enums"
```

---

## Task 4: Domain Policy Value Objects

**Files:**
- Create: `src/Domain/Reddit/ValueObject/PhasePolicy.php`
- Create: `src/Domain/Reddit/ValueObject/ContentMixPolicy.php`
- Create: `src/Domain/Reddit/ValueObject/SubredditCadencePolicy.php`
- Test: `tests/Unit/Domain/Reddit/PhasePolicyTest.php`
- Test: `tests/Unit/Domain/Reddit/ContentMixPolicyTest.php`
- Test: `tests/Unit/Domain/Reddit/SubredditCadencePolicyTest.php`

- [ ] **Step 1: Write PhasePolicy**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

final readonly class PhasePolicy
{
    private const int LURK_DAYS = 14;
    private const int COMMENT_DAYS = 30;

    public function __construct(
        private \DateTimeImmutable $accountCreatedAt,
    ) {}

    public function currentPhase(\DateTimeImmutable $now = new \DateTimeImmutable()): Phase
    {
        $days = $this->accountAgeDays($now);

        if ($days <= self::LURK_DAYS) {
            return Phase::Lurk;
        }

        if ($days <= self::COMMENT_DAYS) {
            return Phase::Comment;
        }

        return Phase::Full;
    }

    public function accountAgeDays(\DateTimeImmutable $now = new \DateTimeImmutable()): int
    {
        return max(0, $this->accountCreatedAt->diff($now)->days);
    }

    public function canDraft(\DateTimeImmutable $now = new \DateTimeImmutable()): bool
    {
        return $this->currentPhase($now) !== Phase::Lurk;
    }

    public function canPost(\DateTimeImmutable $now = new \DateTimeImmutable()): bool
    {
        return $this->currentPhase($now) === Phase::Full;
    }
}
```

- [ ] **Step 2: Write PhasePolicyTest**

```php
<?php

namespace Tests\Unit\Domain\Reddit;

use Domain\Reddit\ValueObject\Phase;
use Domain\Reddit\ValueObject\PhasePolicy;
use PHPUnit\Framework\TestCase;

class PhasePolicyTest extends TestCase
{
    public function test_lurk_phase_during_first_14_days(): void
    {
        $policy = new PhasePolicy(new \DateTimeImmutable('2026-03-01'));
        $now = new \DateTimeImmutable('2026-03-10');

        $this->assertSame(Phase::Lurk, $policy->currentPhase($now));
        $this->assertFalse($policy->canDraft($now));
        $this->assertFalse($policy->canPost($now));
    }

    public function test_comment_phase_between_day_15_and_30(): void
    {
        $policy = new PhasePolicy(new \DateTimeImmutable('2026-03-01'));
        $now = new \DateTimeImmutable('2026-03-20');

        $this->assertSame(Phase::Comment, $policy->currentPhase($now));
        $this->assertTrue($policy->canDraft($now));
        $this->assertFalse($policy->canPost($now));
    }

    public function test_full_phase_after_day_30(): void
    {
        $policy = new PhasePolicy(new \DateTimeImmutable('2026-03-01'));
        $now = new \DateTimeImmutable('2026-04-15');

        $this->assertSame(Phase::Full, $policy->currentPhase($now));
        $this->assertTrue($policy->canDraft($now));
        $this->assertTrue($policy->canPost($now));
    }

    public function test_account_age_days(): void
    {
        $policy = new PhasePolicy(new \DateTimeImmutable('2026-03-01'));
        $this->assertSame(10, $policy->accountAgeDays(new \DateTimeImmutable('2026-03-11')));
    }

    public function test_boundary_day_14_is_lurk(): void
    {
        $policy = new PhasePolicy(new \DateTimeImmutable('2026-03-01'));
        $this->assertSame(Phase::Lurk, $policy->currentPhase(new \DateTimeImmutable('2026-03-15')));
    }

    public function test_boundary_day_15_is_comment(): void
    {
        $policy = new PhasePolicy(new \DateTimeImmutable('2026-03-01'));
        $this->assertSame(Phase::Comment, $policy->currentPhase(new \DateTimeImmutable('2026-03-16')));
    }
}
```

- [ ] **Step 3: Run PhasePolicyTest**

Run: `./vendor/bin/sail artisan test --filter=PhasePolicyTest`
Expected: PASS (6 tests)

- [ ] **Step 4: Write ContentMixPolicy**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

final readonly class ContentMixPolicy
{
    private const float VALUE_TARGET = 0.70;
    private const float DISCUSSION_TARGET = 0.20;
    private const float BRAND_TARGET = 0.10;

    /**
     * @param array{value: int, discussion: int, brand: int} $counts 30-day rolling counts
     */
    public function __construct(
        private array $counts,
    ) {}

    public function total(): int
    {
        return $this->counts['value'] + $this->counts['discussion'] + $this->counts['brand'];
    }

    public function ratio(ContentCategory $category): float
    {
        $total = $this->total();
        if ($total === 0) {
            return 0.0;
        }

        return $this->counts[$category->value] / $total;
    }

    public function canGenerate(ContentCategory $category): bool
    {
        $total = $this->total();

        // Always allow if fewer than 5 drafts in window (not enough data to enforce)
        if ($total < 5) {
            return true;
        }

        $target = match ($category) {
            ContentCategory::Value => self::VALUE_TARGET,
            ContentCategory::Discussion => self::DISCUSSION_TARGET,
            ContentCategory::Brand => self::BRAND_TARGET,
        };

        return $this->ratio($category) <= $target + 0.05; // 5% tolerance
    }

    /**
     * Suggest the best content category to bring ratios closer to target.
     */
    public function suggestCategory(): ContentCategory
    {
        $total = $this->total();

        if ($total < 5) {
            return ContentCategory::Value;
        }

        $gaps = [
            ContentCategory::Value->value => self::VALUE_TARGET - $this->ratio(ContentCategory::Value),
            ContentCategory::Discussion->value => self::DISCUSSION_TARGET - $this->ratio(ContentCategory::Discussion),
            ContentCategory::Brand->value => self::BRAND_TARGET - $this->ratio(ContentCategory::Brand),
        ];

        $best = array_keys($gaps, max($gaps))[0];

        return ContentCategory::from($best);
    }
}
```

- [ ] **Step 5: Write ContentMixPolicyTest**

```php
<?php

namespace Tests\Unit\Domain\Reddit;

use Domain\Reddit\ValueObject\ContentCategory;
use Domain\Reddit\ValueObject\ContentMixPolicy;
use PHPUnit\Framework\TestCase;

class ContentMixPolicyTest extends TestCase
{
    public function test_allows_any_category_when_fewer_than_5_drafts(): void
    {
        $policy = new ContentMixPolicy(['value' => 0, 'discussion' => 0, 'brand' => 2]);

        $this->assertTrue($policy->canGenerate(ContentCategory::Brand));
        $this->assertTrue($policy->canGenerate(ContentCategory::Value));
    }

    public function test_blocks_brand_when_over_10_percent(): void
    {
        // 7 value, 1 discussion, 2 brand = 20% brand (over 10% + 5% tolerance)
        $policy = new ContentMixPolicy(['value' => 7, 'discussion' => 1, 'brand' => 2]);

        $this->assertFalse($policy->canGenerate(ContentCategory::Brand));
    }

    public function test_allows_brand_when_at_or_under_target(): void
    {
        // 7 value, 2 discussion, 1 brand = 10% brand (exactly at target)
        $policy = new ContentMixPolicy(['value' => 7, 'discussion' => 2, 'brand' => 1]);

        $this->assertTrue($policy->canGenerate(ContentCategory::Brand));
    }

    public function test_suggests_value_when_empty(): void
    {
        $policy = new ContentMixPolicy(['value' => 0, 'discussion' => 0, 'brand' => 0]);

        $this->assertSame(ContentCategory::Value, $policy->suggestCategory());
    }

    public function test_suggests_underrepresented_category(): void
    {
        // 3 value, 5 discussion, 2 brand = value at 30%, way below 70% target
        $policy = new ContentMixPolicy(['value' => 3, 'discussion' => 5, 'brand' => 2]);

        $this->assertSame(ContentCategory::Value, $policy->suggestCategory());
    }

    public function test_ratio_calculation(): void
    {
        $policy = new ContentMixPolicy(['value' => 7, 'discussion' => 2, 'brand' => 1]);

        $this->assertEqualsWithDelta(0.70, $policy->ratio(ContentCategory::Value), 0.01);
        $this->assertEqualsWithDelta(0.20, $policy->ratio(ContentCategory::Discussion), 0.01);
        $this->assertEqualsWithDelta(0.10, $policy->ratio(ContentCategory::Brand), 0.01);
    }
}
```

- [ ] **Step 6: Run ContentMixPolicyTest**

Run: `./vendor/bin/sail artisan test --filter=ContentMixPolicyTest`
Expected: PASS (6 tests)

- [ ] **Step 7: Write SubredditCadencePolicy**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

final readonly class SubredditCadencePolicy
{
    public function __construct(
        private int $maxPostsPerWeek,
        private int $maxCommentsPerWeek,
        private int $postsThisWeek,
        private int $commentsThisWeek,
    ) {}

    public function canPost(): bool
    {
        return $this->postsThisWeek < $this->maxPostsPerWeek;
    }

    public function canComment(): bool
    {
        return $this->commentsThisWeek < $this->maxCommentsPerWeek;
    }

    public function canPublish(DraftType $type): bool
    {
        return match ($type) {
            DraftType::Post => $this->canPost(),
            DraftType::Comment => $this->canComment(),
        };
    }
}
```

- [ ] **Step 8: Write SubredditCadencePolicyTest**

```php
<?php

namespace Tests\Unit\Domain\Reddit;

use Domain\Reddit\ValueObject\DraftType;
use Domain\Reddit\ValueObject\SubredditCadencePolicy;
use PHPUnit\Framework\TestCase;

class SubredditCadencePolicyTest extends TestCase
{
    public function test_allows_post_when_under_limit(): void
    {
        $policy = new SubredditCadencePolicy(maxPostsPerWeek: 2, maxCommentsPerWeek: 5, postsThisWeek: 1, commentsThisWeek: 0);

        $this->assertTrue($policy->canPost());
        $this->assertTrue($policy->canPublish(DraftType::Post));
    }

    public function test_blocks_post_when_at_limit(): void
    {
        $policy = new SubredditCadencePolicy(maxPostsPerWeek: 2, maxCommentsPerWeek: 5, postsThisWeek: 2, commentsThisWeek: 0);

        $this->assertFalse($policy->canPost());
        $this->assertFalse($policy->canPublish(DraftType::Post));
    }

    public function test_allows_comment_when_under_limit(): void
    {
        $policy = new SubredditCadencePolicy(maxPostsPerWeek: 2, maxCommentsPerWeek: 5, postsThisWeek: 0, commentsThisWeek: 3);

        $this->assertTrue($policy->canComment());
        $this->assertTrue($policy->canPublish(DraftType::Comment));
    }

    public function test_blocks_comment_when_at_limit(): void
    {
        $policy = new SubredditCadencePolicy(maxPostsPerWeek: 2, maxCommentsPerWeek: 5, postsThisWeek: 0, commentsThisWeek: 5);

        $this->assertFalse($policy->canComment());
        $this->assertFalse($policy->canPublish(DraftType::Comment));
    }
}
```

- [ ] **Step 9: Run SubredditCadencePolicyTest**

Run: `./vendor/bin/sail artisan test --filter=SubredditCadencePolicyTest`
Expected: PASS (4 tests)

- [ ] **Step 10: Commit**

```bash
git add src/Domain/Reddit/ValueObject/PhasePolicy.php src/Domain/Reddit/ValueObject/ContentMixPolicy.php src/Domain/Reddit/ValueObject/SubredditCadencePolicy.php tests/Unit/Domain/Reddit/
git commit -m "feat(reddit): add domain policy value objects with tests"
```

---

## Task 5: Domain Entities

**Files:**
- Create: `src/Domain/Reddit/Entity/RedditThread.php`
- Create: `src/Domain/Reddit/Entity/RedditDraft.php`
- Create: `src/Domain/Reddit/Entity/RedditSubreddit.php`
- Create: `src/Domain/Reddit/Entity/RedditStrategyReport.php`

- [ ] **Step 1: Write RedditThread entity**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\Entity;

use Domain\Reddit\ValueObject\ThreadStatus;
use Domain\Reddit\ValueObject\ThreadType;

final class RedditThread
{
    public function __construct(
        public readonly int $id,
        public readonly int $subredditId,
        public readonly string $redditId,
        public readonly string $title,
        public readonly ?string $body,
        public readonly string $author,
        public readonly string $url,
        public readonly int $score,
        public readonly int $numComments,
        public readonly ThreadType $threadType,
        public ThreadStatus $status,
        public readonly \DateTimeImmutable $discoveredAt,
        public readonly \DateTimeImmutable $createdAt,
    ) {}

    public function markStale(): void
    {
        $this->status = ThreadStatus::Stale;
    }

    public function markDrafted(): void
    {
        $this->status = ThreadStatus::Drafted;
    }

    public function isStale(\DateTimeImmutable $now = new \DateTimeImmutable()): bool
    {
        $hoursSinceDiscovery = ($now->getTimestamp() - $this->discoveredAt->getTimestamp()) / 3600;
        return $hoursSinceDiscovery > 24;
    }
}
```

- [ ] **Step 2: Write RedditDraft entity**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\Entity;

use Domain\Reddit\ValueObject\ContentCategory;
use Domain\Reddit\ValueObject\DraftStatus;
use Domain\Reddit\ValueObject\DraftType;

final class RedditDraft
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $threadId,
        public readonly int $subredditId,
        public readonly DraftType $type,
        public readonly ContentCategory $contentCategory,
        public readonly ?string $title,
        public string $body,
        public DraftStatus $status,
        public ?string $redditThingId,
        public ?\DateTimeImmutable $publishedAt,
        public ?\DateTimeImmutable $approvedAt,
        public ?\DateTimeImmutable $rejectedAt,
        public ?string $rejectionReason,
        public ?int $redditScore,
        public ?int $redditNumComments,
        public readonly \DateTimeImmutable $createdAt,
    ) {}

    public function approve(): void
    {
        $this->status = DraftStatus::Approved;
        $this->approvedAt = new \DateTimeImmutable();
    }

    public function reject(string $reason): void
    {
        $this->status = DraftStatus::Rejected;
        $this->rejectedAt = new \DateTimeImmutable();
        $this->rejectionReason = $reason;
    }

    public function markPublished(string $redditThingId): void
    {
        $this->status = DraftStatus::Published;
        $this->redditThingId = $redditThingId;
        $this->publishedAt = new \DateTimeImmutable();
    }

    public function markFailed(): void
    {
        $this->status = DraftStatus::Failed;
    }

    public function retry(): void
    {
        $this->status = DraftStatus::Approved;
    }
}
```

- [ ] **Step 3: Write RedditSubreddit entity**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\Entity;

final class RedditSubreddit
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $tier,
        public int $maxPostsPerWeek,
        public int $maxCommentsPerWeek,
        public ?array $rulesJson,
        public ?array $keywordsJson,
        public bool $isActive,
    ) {}
}
```

- [ ] **Step 4: Write RedditStrategyReport entity**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\Entity;

final class RedditStrategyReport
{
    public function __construct(
        public readonly int $id,
        public readonly \DateTimeImmutable $periodStart,
        public readonly \DateTimeImmutable $periodEnd,
        public readonly array $reportJson,
        public readonly array $recommendationsJson,
        public readonly array $contentRatioJson,
        public readonly array $topPerformingJson,
        public readonly \DateTimeImmutable $createdAt,
    ) {}
}
```

- [ ] **Step 5: Commit**

```bash
git add src/Domain/Reddit/Entity/
git commit -m "feat(reddit): add domain entities"
```

---

## Task 6: Domain Port Interfaces

**Files:**
- Create: `src/Domain/Reddit/Port/RedditApiInterface.php`
- Create: `src/Domain/Reddit/Port/AiDrafterInterface.php`
- Create: `src/Domain/Reddit/Port/RedditThreadRepositoryInterface.php`
- Create: `src/Domain/Reddit/Port/RedditDraftRepositoryInterface.php`
- Create: `src/Domain/Reddit/Port/RedditSubredditRepositoryInterface.php`
- Create: `src/Domain/Reddit/Port/RedditStrategyReportRepositoryInterface.php`

- [ ] **Step 1: Write RedditApiInterface**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

interface RedditApiInterface
{
    /**
     * Search a subreddit for threads matching a query.
     *
     * @return array<array{id: string, title: string, selftext: string|null, author: string, url: string, score: int, num_comments: int, created_utc: int}>
     */
    public function searchSubreddit(string $subreddit, string $query, int $limit = 25): array;

    /**
     * Submit a comment as a reply to a thread.
     *
     * @return string The Reddit thing ID of the submitted comment
     */
    public function submitComment(string $parentThingId, string $body): string;

    /**
     * Submit a new text post to a subreddit.
     *
     * @return string The Reddit thing ID of the submitted post
     */
    public function submitPost(string $subreddit, string $title, string $body): string;
}
```

- [ ] **Step 2: Write AiDrafterInterface**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

use Domain\Reddit\Entity\RedditThread;
use Domain\Reddit\ValueObject\ContentCategory;
use Domain\Reddit\ValueObject\DraftType;

interface AiDrafterInterface
{
    public function generateDraft(
        RedditThread $thread,
        string $subredditName,
        ?array $subredditRules,
        ContentCategory $contentCategory,
        DraftType $draftType,
    ): string;
}
```

- [ ] **Step 3: Write RedditThreadRepositoryInterface**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

use Domain\Reddit\Entity\RedditThread;

interface RedditThreadRepositoryInterface
{
    public function findById(int $id): ?RedditThread;

    public function findByRedditId(string $redditId): ?RedditThread;

    /** @return RedditThread[] */
    public function findNewThreads(int $limit = 10): array;

    /**
     * Save a thread. Returns the saved entity with the real DB id.
     */
    public function save(RedditThread $thread): RedditThread;

    public function markStaleThreads(\DateTimeImmutable $olderThan): int;

    public function purgeOlderThan(\DateTimeImmutable $date, array $statuses): int;
}
```

- [ ] **Step 4: Write RedditDraftRepositoryInterface**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

use Domain\Reddit\Entity\RedditDraft;

interface RedditDraftRepositoryInterface
{
    public function findById(int $id): ?RedditDraft;

    /** @return RedditDraft[] */
    public function findByStatus(string $status, int $limit = 50): array;

    /**
     * Save a draft. Returns the saved entity with the real DB id.
     */
    public function save(RedditDraft $draft): RedditDraft;

    /**
     * Count drafts by content category in a date range.
     *
     * @return array{value: int, discussion: int, brand: int}
     */
    public function countByContentCategoryBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): array;

    /**
     * Count published drafts for a subreddit in the current week.
     *
     * @return array{posts: int, comments: int}
     */
    public function countPublishedThisWeek(int $subredditId): array;

    public function purgeOlderThan(\DateTimeImmutable $date, array $statuses): int;
}
```

- [ ] **Step 5: Write RedditSubredditRepositoryInterface**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

use Domain\Reddit\Entity\RedditSubreddit;

interface RedditSubredditRepositoryInterface
{
    public function findById(int $id): ?RedditSubreddit;

    /** @return RedditSubreddit[] */
    public function findActive(): array;

    /** @return RedditSubreddit[] */
    public function findAll(): array;

    public function save(RedditSubreddit $subreddit): void;
}
```

- [ ] **Step 6: Write AiStrategistInterface**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

interface AiStrategistInterface
{
    /**
     * Generate a strategy analysis from weekly metrics.
     *
     * @return array{summary: string, working_well: array, needs_improvement: array, recommendations: array, phase_assessment: string}
     */
    public function analyzeWeeklyMetrics(array $metricsContext): array;
}
```

- [ ] **Step 7: Write RedditStrategyReportRepositoryInterface**

```php
<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

use Domain\Reddit\Entity\RedditStrategyReport;

interface RedditStrategyReportRepositoryInterface
{
    public function findById(int $id): ?RedditStrategyReport;

    public function findLatest(): ?RedditStrategyReport;

    /** @return RedditStrategyReport[] */
    public function findAll(): array;

    public function save(RedditStrategyReport $report): void;
}
```

- [ ] **Step 8: Commit**

```bash
git add src/Domain/Reddit/Port/
git commit -m "feat(reddit): add domain port interfaces"
```

---

## Task 7: Eloquent Models

**Files:**
- Create: `app/Infrastructure/Persistence/Eloquent/RedditSubredditModel.php`
- Create: `app/Infrastructure/Persistence/Eloquent/RedditThreadModel.php`
- Create: `app/Infrastructure/Persistence/Eloquent/RedditDraftModel.php`
- Create: `app/Infrastructure/Persistence/Eloquent/RedditStrategyReportModel.php`

- [ ] **Step 1: Write RedditSubredditModel**

```php
<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedditSubredditModel extends Model
{
    protected $table = 'reddit_subreddits';

    protected $fillable = [
        'name',
        'tier',
        'max_posts_per_week',
        'max_comments_per_week',
        'rules_json',
        'keywords_json',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rules_json' => 'array',
            'keywords_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function threads(): HasMany
    {
        return $this->hasMany(RedditThreadModel::class, 'reddit_subreddit_id');
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(RedditDraftModel::class, 'reddit_subreddit_id');
    }
}
```

- [ ] **Step 2: Write RedditThreadModel**

```php
<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedditThreadModel extends Model
{
    protected $table = 'reddit_threads';

    protected $fillable = [
        'reddit_subreddit_id',
        'reddit_id',
        'title',
        'body',
        'author',
        'url',
        'score',
        'num_comments',
        'thread_type',
        'status',
        'discovered_at',
    ];

    protected function casts(): array
    {
        return [
            'discovered_at' => 'datetime',
        ];
    }

    public function subreddit(): BelongsTo
    {
        return $this->belongsTo(RedditSubredditModel::class, 'reddit_subreddit_id');
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(RedditDraftModel::class, 'reddit_thread_id');
    }
}
```

- [ ] **Step 3: Write RedditDraftModel**

```php
<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedditDraftModel extends Model
{
    protected $table = 'reddit_drafts';

    protected $fillable = [
        'reddit_thread_id',
        'reddit_subreddit_id',
        'type',
        'content_category',
        'title',
        'body',
        'status',
        'reddit_thing_id',
        'published_at',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'reddit_score',
        'reddit_num_comments',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(RedditThreadModel::class, 'reddit_thread_id');
    }

    public function subreddit(): BelongsTo
    {
        return $this->belongsTo(RedditSubredditModel::class, 'reddit_subreddit_id');
    }
}
```

- [ ] **Step 4: Write RedditStrategyReportModel**

```php
<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class RedditStrategyReportModel extends Model
{
    protected $table = 'reddit_strategy_reports';

    protected $fillable = [
        'period_start',
        'period_end',
        'report_json',
        'recommendations_json',
        'content_ratio_json',
        'top_performing_json',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'report_json' => 'array',
            'recommendations_json' => 'array',
            'content_ratio_json' => 'array',
            'top_performing_json' => 'array',
        ];
    }
}
```

- [ ] **Step 5: Commit**

```bash
git add app/Infrastructure/Persistence/Eloquent/Reddit*.php
git commit -m "feat(reddit): add Eloquent models"
```

---

## Task 8: Eloquent Repositories

**Files:**
- Create: `app/Infrastructure/Persistence/Repository/EloquentRedditThreadRepository.php`
- Create: `app/Infrastructure/Persistence/Repository/EloquentRedditDraftRepository.php`
- Create: `app/Infrastructure/Persistence/Repository/EloquentRedditSubredditRepository.php`
- Create: `app/Infrastructure/Persistence/Repository/EloquentRedditStrategyReportRepository.php`
- Modify: `app/Providers/DomainServiceProvider.php`

- [ ] **Step 1: Write EloquentRedditSubredditRepository**

```php
<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use Domain\Reddit\Entity\RedditSubreddit;
use Domain\Reddit\Port\RedditSubredditRepositoryInterface;

class EloquentRedditSubredditRepository implements RedditSubredditRepositoryInterface
{
    public function findById(int $id): ?RedditSubreddit
    {
        $model = RedditSubredditModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findActive(): array
    {
        return RedditSubredditModel::where('is_active', true)
            ->orderBy('tier')
            ->get()
            ->map(fn (RedditSubredditModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findAll(): array
    {
        return RedditSubredditModel::orderBy('tier')
            ->orderBy('name')
            ->get()
            ->map(fn (RedditSubredditModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(RedditSubreddit $subreddit): void
    {
        RedditSubredditModel::updateOrCreate(
            $subreddit->id > 0 ? ['id' => $subreddit->id] : ['name' => $subreddit->name],
            [
                'name' => $subreddit->name,
                'tier' => $subreddit->tier,
                'max_posts_per_week' => $subreddit->maxPostsPerWeek,
                'max_comments_per_week' => $subreddit->maxCommentsPerWeek,
                'rules_json' => $subreddit->rulesJson,
                'keywords_json' => $subreddit->keywordsJson,
                'is_active' => $subreddit->isActive,
            ]
        );
    }

    private function toDomain(RedditSubredditModel $model): RedditSubreddit
    {
        return new RedditSubreddit(
            id: $model->id,
            name: $model->name,
            tier: $model->tier,
            maxPostsPerWeek: $model->max_posts_per_week,
            maxCommentsPerWeek: $model->max_comments_per_week,
            rulesJson: $model->rules_json,
            keywordsJson: $model->keywords_json,
            isActive: $model->is_active,
        );
    }
}
```

- [ ] **Step 2: Write EloquentRedditThreadRepository**

```php
<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use Domain\Reddit\Entity\RedditThread;
use Domain\Reddit\Port\RedditThreadRepositoryInterface;
use Domain\Reddit\ValueObject\ThreadStatus;
use Domain\Reddit\ValueObject\ThreadType;

class EloquentRedditThreadRepository implements RedditThreadRepositoryInterface
{
    public function findById(int $id): ?RedditThread
    {
        $model = RedditThreadModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByRedditId(string $redditId): ?RedditThread
    {
        $model = RedditThreadModel::where('reddit_id', $redditId)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findNewThreads(int $limit = 10): array
    {
        return RedditThreadModel::where('status', ThreadStatus::New->value)
            ->orderBy('discovered_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn (RedditThreadModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(RedditThread $thread): RedditThread
    {
        $attributes = [
            'reddit_subreddit_id' => $thread->subredditId,
            'reddit_id' => $thread->redditId,
            'title' => $thread->title,
            'body' => $thread->body,
            'author' => $thread->author,
            'url' => $thread->url,
            'score' => $thread->score,
            'num_comments' => $thread->numComments,
            'thread_type' => $thread->threadType->value,
            'status' => $thread->status->value,
            'discovered_at' => $thread->discoveredAt,
        ];

        // Use reddit_id as the natural key for upserts (new entities have id=0)
        $model = $thread->id > 0
            ? RedditThreadModel::updateOrCreate(['id' => $thread->id], $attributes)
            : RedditThreadModel::updateOrCreate(['reddit_id' => $thread->redditId], $attributes);

        return $this->toDomain($model);
    }

    public function markStaleThreads(\DateTimeImmutable $olderThan): int
    {
        return RedditThreadModel::where('status', ThreadStatus::New->value)
            ->where('discovered_at', '<', $olderThan->format('Y-m-d H:i:s'))
            ->update(['status' => ThreadStatus::Stale->value]);
    }

    public function purgeOlderThan(\DateTimeImmutable $date, array $statuses): int
    {
        return RedditThreadModel::whereIn('status', array_map(fn ($s) => $s->value, $statuses))
            ->where('created_at', '<', $date->format('Y-m-d H:i:s'))
            ->delete();
    }

    private function toDomain(RedditThreadModel $model): RedditThread
    {
        return new RedditThread(
            id: $model->id,
            subredditId: $model->reddit_subreddit_id,
            redditId: $model->reddit_id,
            title: $model->title,
            body: $model->body,
            author: $model->author,
            url: $model->url,
            score: $model->score,
            numComments: $model->num_comments,
            threadType: ThreadType::from($model->thread_type),
            status: ThreadStatus::from($model->status),
            discoveredAt: \DateTimeImmutable::createFromInterface($model->discovered_at),
            createdAt: \DateTimeImmutable::createFromInterface($model->created_at),
        );
    }
}
```

- [ ] **Step 3: Write EloquentRedditDraftRepository**

```php
<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use Domain\Reddit\Entity\RedditDraft;
use Domain\Reddit\Port\RedditDraftRepositoryInterface;
use Domain\Reddit\ValueObject\ContentCategory;
use Domain\Reddit\ValueObject\DraftStatus;
use Domain\Reddit\ValueObject\DraftType;

class EloquentRedditDraftRepository implements RedditDraftRepositoryInterface
{
    public function findById(int $id): ?RedditDraft
    {
        $model = RedditDraftModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByStatus(string $status, int $limit = 50): array
    {
        return RedditDraftModel::where('status', $status)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn (RedditDraftModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(RedditDraft $draft): RedditDraft
    {
        $attributes = [
            'reddit_thread_id' => $draft->threadId,
            'reddit_subreddit_id' => $draft->subredditId,
            'type' => $draft->type->value,
            'content_category' => $draft->contentCategory->value,
            'title' => $draft->title,
            'body' => $draft->body,
            'status' => $draft->status->value,
            'reddit_thing_id' => $draft->redditThingId,
            'published_at' => $draft->publishedAt,
            'approved_at' => $draft->approvedAt,
            'rejected_at' => $draft->rejectedAt,
            'rejection_reason' => $draft->rejectionReason,
            'reddit_score' => $draft->redditScore,
            'reddit_num_comments' => $draft->redditNumComments,
        ];

        if ($draft->id > 0) {
            $model = RedditDraftModel::updateOrCreate(['id' => $draft->id], $attributes);
        } else {
            $model = RedditDraftModel::create($attributes);
        }

        return $this->toDomain($model);
    }

    public function countByContentCategoryBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $counts = RedditDraftModel::whereBetween('created_at', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')])
            ->whereIn('status', [DraftStatus::Pending->value, DraftStatus::Approved->value, DraftStatus::Published->value])
            ->selectRaw('content_category, COUNT(*) as count')
            ->groupBy('content_category')
            ->pluck('count', 'content_category')
            ->toArray();

        return [
            'value' => $counts[ContentCategory::Value->value] ?? 0,
            'discussion' => $counts[ContentCategory::Discussion->value] ?? 0,
            'brand' => $counts[ContentCategory::Brand->value] ?? 0,
        ];
    }

    public function countPublishedThisWeek(int $subredditId): array
    {
        $weekStart = now()->startOfWeek();

        $counts = RedditDraftModel::where('reddit_subreddit_id', $subredditId)
            ->where('status', DraftStatus::Published->value)
            ->where('published_at', '>=', $weekStart)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'posts' => $counts[DraftType::Post->value] ?? 0,
            'comments' => $counts[DraftType::Comment->value] ?? 0,
        ];
    }

    public function purgeOlderThan(\DateTimeImmutable $date, array $statuses): int
    {
        return RedditDraftModel::whereIn('status', array_map(fn ($s) => $s->value, $statuses))
            ->where('created_at', '<', $date->format('Y-m-d H:i:s'))
            ->delete();
    }

    private function toDomain(RedditDraftModel $model): RedditDraft
    {
        return new RedditDraft(
            id: $model->id,
            threadId: $model->reddit_thread_id,
            subredditId: $model->reddit_subreddit_id,
            type: DraftType::from($model->type),
            contentCategory: ContentCategory::from($model->content_category),
            title: $model->title,
            body: $model->body,
            status: DraftStatus::from($model->status),
            redditThingId: $model->reddit_thing_id,
            publishedAt: $model->published_at ? \DateTimeImmutable::createFromInterface($model->published_at) : null,
            approvedAt: $model->approved_at ? \DateTimeImmutable::createFromInterface($model->approved_at) : null,
            rejectedAt: $model->rejected_at ? \DateTimeImmutable::createFromInterface($model->rejected_at) : null,
            rejectionReason: $model->rejection_reason,
            redditScore: $model->reddit_score,
            redditNumComments: $model->reddit_num_comments,
            createdAt: \DateTimeImmutable::createFromInterface($model->created_at),
        );
    }
}
```

- [ ] **Step 4: Write EloquentRedditStrategyReportRepository**

```php
<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\RedditStrategyReportModel;
use Domain\Reddit\Entity\RedditStrategyReport;
use Domain\Reddit\Port\RedditStrategyReportRepositoryInterface;

class EloquentRedditStrategyReportRepository implements RedditStrategyReportRepositoryInterface
{
    public function findById(int $id): ?RedditStrategyReport
    {
        $model = RedditStrategyReportModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findLatest(): ?RedditStrategyReport
    {
        $model = RedditStrategyReportModel::orderByDesc('period_end')->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findAll(): array
    {
        return RedditStrategyReportModel::orderByDesc('period_end')
            ->get()
            ->map(fn (RedditStrategyReportModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(RedditStrategyReport $report): void
    {
        $key = $report->id > 0 ? ['id' => $report->id] : ['period_start' => $report->periodStart->format('Y-m-d'), 'period_end' => $report->periodEnd->format('Y-m-d')];
        RedditStrategyReportModel::updateOrCreate(
            $key,
            [
                'period_start' => $report->periodStart->format('Y-m-d'),
                'period_end' => $report->periodEnd->format('Y-m-d'),
                'report_json' => $report->reportJson,
                'recommendations_json' => $report->recommendationsJson,
                'content_ratio_json' => $report->contentRatioJson,
                'top_performing_json' => $report->topPerformingJson,
            ]
        );
    }

    private function toDomain(RedditStrategyReportModel $model): RedditStrategyReport
    {
        return new RedditStrategyReport(
            id: $model->id,
            periodStart: \DateTimeImmutable::createFromInterface($model->period_start),
            periodEnd: \DateTimeImmutable::createFromInterface($model->period_end),
            reportJson: $model->report_json,
            recommendationsJson: $model->recommendations_json,
            contentRatioJson: $model->content_ratio_json,
            topPerformingJson: $model->top_performing_json,
            createdAt: \DateTimeImmutable::createFromInterface($model->created_at),
        );
    }
}
```

- [ ] **Step 5: Register bindings in DomainServiceProvider**

Add these imports and bindings to `app/Providers/DomainServiceProvider.php`:

```php
// Add to imports:
use App\Infrastructure\Persistence\Repository\EloquentRedditDraftRepository;
use App\Infrastructure\Persistence\Repository\EloquentRedditStrategyReportRepository;
use App\Infrastructure\Persistence\Repository\EloquentRedditSubredditRepository;
use App\Infrastructure\Persistence\Repository\EloquentRedditThreadRepository;
use Domain\Reddit\Port\RedditDraftRepositoryInterface;
use Domain\Reddit\Port\RedditStrategyReportRepositoryInterface;
use Domain\Reddit\Port\RedditSubredditRepositoryInterface;
use Domain\Reddit\Port\RedditThreadRepositoryInterface;

// Add to $bindings array:
RedditThreadRepositoryInterface::class => EloquentRedditThreadRepository::class,
RedditDraftRepositoryInterface::class => EloquentRedditDraftRepository::class,
RedditSubredditRepositoryInterface::class => EloquentRedditSubredditRepository::class,
RedditStrategyReportRepositoryInterface::class => EloquentRedditStrategyReportRepository::class,
```

Note: `RedditApiInterface` and `AiDrafterInterface` bindings will be added in Task 10 when those implementations are built.

- [ ] **Step 6: Commit**

```bash
git add app/Infrastructure/Persistence/Repository/EloquentReddit*.php app/Providers/DomainServiceProvider.php
git commit -m "feat(reddit): add Eloquent repositories and DomainServiceProvider bindings"
```

---

## Task 9: Subreddit Seeder

**Files:**
- Create: `database/seeders/RedditSubredditSeeder.php`

- [ ] **Step 1: Write seeder with subreddits from playbook**

```php
<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use Illuminate\Database\Seeder;

class RedditSubredditSeeder extends Seeder
{
    public function run(): void
    {
        $subreddits = [
            // Tier 1 -- Primary Communities (Daily)
            ['name' => 'smallbusiness', 'tier' => 1, 'max_posts_per_week' => 2, 'max_comments_per_week' => 7, 'rules_json' => ['no_direct_links' => true, 'self_promo_in_comments_only' => true], 'keywords_json' => ['get more reviews', 'negative review', 'google reviews', 'customer feedback', 'reputation management']],
            ['name' => 'Entrepreneur', 'tier' => 1, 'max_posts_per_week' => 1, 'max_comments_per_week' => 5, 'rules_json' => ['no_self_links' => true, 'weekly_promo_thread' => true], 'keywords_json' => ['customer reviews', 'feedback', 'local business', 'reputation']],
            ['name' => 'SEO', 'tier' => 1, 'max_posts_per_week' => 1, 'max_comments_per_week' => 5, 'rules_json' => ['no_promo' => true, 'case_studies_welcome' => true], 'keywords_json' => ['local seo', 'google reviews', 'review velocity', 'local pack', 'google business profile']],

            // Tier 2 -- Industry-Specific (3-4x per week)
            ['name' => 'plumbing', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 4, 'keywords_json' => ['more work', 'reputation', 'reviews', 'customers']],
            ['name' => 'dentistry', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 4, 'keywords_json' => ['patient reviews', 'practice growth', 'online reputation']],
            ['name' => 'Cleaning', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['cleaning business', 'reviews', 'reputation', 'customers']],
            ['name' => 'HVAC', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 4, 'keywords_json' => ['business growth', 'reviews', 'customers', 'reputation']],
            ['name' => 'electricians', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 4, 'keywords_json' => ['business', 'reviews', 'customers', 'reputation']],
            ['name' => 'landscaping', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['business growth', 'reviews', 'customers']],
            ['name' => 'salons', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['salon business', 'reviews', 'reputation']],
            ['name' => 'HairStylist', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['salon', 'reviews', 'clients', 'business']],

            // Tier 3 -- Marketing and SaaS (2x per week)
            ['name' => 'marketing', 'tier' => 3, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'rules_json' => ['no_self_promo' => true], 'keywords_json' => ['reputation management', 'customer reviews', 'local marketing', 'review management']],
            ['name' => 'digital_marketing', 'tier' => 3, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['review management', 'local seo', 'reputation', 'client reviews']],
            ['name' => 'SaaS', 'tier' => 3, 'max_posts_per_week' => 1, 'max_comments_per_week' => 2, 'rules_json' => ['product_launches_in_threads' => true], 'keywords_json' => ['review tool', 'saas launch', 'customer feedback']],
            ['name' => 'startups', 'tier' => 3, 'max_posts_per_week' => 1, 'max_comments_per_week' => 2, 'rules_json' => ['strict_self_promo' => true, 'share_saturday' => true], 'keywords_json' => ['startup launch', 'feedback', 'b2b saas']],

            // Tier 4 -- Local Business and Niche (1x per week)
            ['name' => 'sweatystartup', 'tier' => 4, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['reviews', 'customers', 'reputation', 'local business']],
            ['name' => 'localbusiness', 'tier' => 4, 'max_posts_per_week' => 1, 'max_comments_per_week' => 2, 'keywords_json' => ['reviews', 'google', 'customers']],
            ['name' => 'GoogleMyBusiness', 'tier' => 4, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['reviews', 'review link', 'google reviews', 'profile optimization']],
            ['name' => 'HomeImprovement', 'tier' => 4, 'max_posts_per_week' => 0, 'max_comments_per_week' => 2, 'keywords_json' => ['contractor reviews', 'finding contractor', 'recommendations']],
        ];

        foreach ($subreddits as $sub) {
            RedditSubredditModel::updateOrCreate(
                ['name' => $sub['name']],
                $sub
            );
        }
    }
}
```

- [ ] **Step 2: Run seeder**

Run: `./vendor/bin/sail artisan db:seed --class=RedditSubredditSeeder`
Expected: 19 subreddits created.

- [ ] **Step 3: Commit**

```bash
git add database/seeders/RedditSubredditSeeder.php
git commit -m "feat(reddit): add subreddit seeder from playbook"
```

---

## Task 10: Infrastructure Services (Reddit API Client + Claude AI Drafter)

**Files:**
- Create: `app/Infrastructure/Reddit/RedditApiClient.php`
- Create: `app/Infrastructure/Reddit/ClaudeAiDrafter.php`
- Modify: `app/Providers/DomainServiceProvider.php`

- [ ] **Step 1: Write RedditApiClient**

```php
<?php

namespace App\Infrastructure\Reddit;

use Domain\Reddit\Port\RedditApiInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RedditApiClient implements RedditApiInterface
{
    private const string TOKEN_CACHE_KEY = 'reddit_oauth_token';
    private const int TOKEN_TTL_SECONDS = 3300; // 55 minutes

    public function searchSubreddit(string $subreddit, string $query, int $limit = 25): array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->withHeaders(['User-Agent' => config('reddit.user_agent')])
            ->get("https://oauth.reddit.com/r/{$subreddit}/search.json", [
                'q' => $query,
                'restrict_sr' => 'on',
                'sort' => 'new',
                'limit' => $limit,
                't' => 'day',
            ]);

        if ($response->failed()) {
            Log::warning('Reddit API search failed', [
                'subreddit' => $subreddit,
                'status' => $response->status(),
            ]);

            return [];
        }

        $children = $response->json('data.children', []);

        return array_map(fn (array $child) => [
            'id' => $child['data']['name'],
            'title' => $child['data']['title'],
            'selftext' => $child['data']['selftext'] ?? null,
            'author' => $child['data']['author'],
            'url' => 'https://reddit.com' . $child['data']['permalink'],
            'score' => $child['data']['score'],
            'num_comments' => $child['data']['num_comments'],
            'created_utc' => $child['data']['created_utc'],
        ], $children);
    }

    public function submitComment(string $parentThingId, string $body): string
    {
        if (config('reddit.dry_run')) {
            Log::info('Reddit dry run: would submit comment', ['parent' => $parentThingId]);

            return 'dry_run_' . uniqid();
        }

        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->withHeaders(['User-Agent' => config('reddit.user_agent')])
            ->asForm()
            ->post('https://oauth.reddit.com/api/comment', [
                'thing_id' => $parentThingId,
                'text' => $body,
            ]);

        if ($response->failed()) {
            Log::error('Reddit API submit comment failed', ['status' => $response->status()]);
            throw new \RuntimeException('Failed to submit comment: ' . $response->status());
        }

        return $response->json('json.data.things.0.data.name', '');
    }

    public function submitPost(string $subreddit, string $title, string $body): string
    {
        if (config('reddit.dry_run')) {
            Log::info('Reddit dry run: would submit post', ['subreddit' => $subreddit, 'title' => $title]);

            return 'dry_run_' . uniqid();
        }

        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->withHeaders(['User-Agent' => config('reddit.user_agent')])
            ->asForm()
            ->post('https://oauth.reddit.com/api/submit', [
                'sr' => $subreddit,
                'kind' => 'self',
                'title' => $title,
                'text' => $body,
            ]);

        if ($response->failed()) {
            Log::error('Reddit API submit post failed', ['status' => $response->status()]);
            throw new \RuntimeException('Failed to submit post: ' . $response->status());
        }

        return $response->json('json.data.name', '');
    }

    private function getAccessToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, self::TOKEN_TTL_SECONDS, function () {
            $response = Http::asForm()
                ->withBasicAuth(config('reddit.client_id'), config('reddit.client_secret'))
                ->withHeaders(['User-Agent' => config('reddit.user_agent')])
                ->post('https://www.reddit.com/api/v1/access_token', [
                    'grant_type' => 'password',
                    'username' => config('reddit.username'),
                    'password' => config('reddit.password'),
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('Failed to obtain Reddit access token: ' . $response->status());
            }

            return $response->json('access_token');
        });
    }
}
```

- [ ] **Step 2: Write ClaudeAiDrafter**

```php
<?php

namespace App\Infrastructure\Reddit;

use Domain\Reddit\Entity\RedditThread;
use Domain\Reddit\Port\AiDrafterInterface;
use Domain\Reddit\ValueObject\ContentCategory;
use Domain\Reddit\ValueObject\DraftType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeAiDrafter implements AiDrafterInterface
{
    public function generateDraft(
        RedditThread $thread,
        string $subredditName,
        ?array $subredditRules,
        ContentCategory $contentCategory,
        DraftType $draftType,
    ): string {
        $maxWords = $draftType === DraftType::Comment ? 300 : 500;
        $rulesText = $subredditRules ? 'Subreddit rules: ' . json_encode($subredditRules) : 'No special subreddit rules.';

        $brandInstruction = $contentCategory === ContentCategory::Brand
            ? 'You may mention QuickFeedback naturally. Always disclose you built it. Always mention competitors fairly. Always provide a free/manual alternative.'
            : 'Do NOT mention QuickFeedback or any specific product.';

        $prompt = <<<PROMPT
You are writing a Reddit {$draftType->value} as a knowledgeable person who helps local businesses get more Google reviews. You are NOT a bot. You are a real person who happens to have expertise in this area.

Rules:
- Write conversationally, like a real Reddit user
- Be specific, use numbers and examples
- Reference the thread's specific situation
- Match the subreddit's culture and tone
- Keep it under {$maxWords} words
- End comments with an offer to elaborate, not a CTA
- {$rulesText}
- {$brandInstruction}

Content category: {$contentCategory->value}
Thread type: {$thread->threadType->value}
Subreddit: r/{$subredditName}

Thread:
Title: {$thread->title}
Body: {$thread->body}

Write the response:
PROMPT;

        $response = Http::withHeaders([
            'x-api-key' => config('reddit.anthropic_api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => config('reddit.drafter_model'),
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->failed()) {
            Log::error('Claude API draft failed', ['status' => $response->status()]);
            throw new \RuntimeException('Failed to generate draft: ' . $response->status());
        }

        return $response->json('content.0.text', '');
    }
}
```

- [ ] **Step 3: Write ClaudeAiStrategist**

```php
<?php

namespace App\Infrastructure\Reddit;

use Domain\Reddit\Port\AiStrategistInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeAiStrategist implements AiStrategistInterface
{
    public function analyzeWeeklyMetrics(array $metricsContext): array
    {
        $metricsJson = json_encode($metricsContext);

        $prompt = <<<PROMPT
You are a Reddit marketing strategist for QuickFeedback, a review management SaaS for local businesses.

Analyze these weekly metrics and provide strategic recommendations:

{$metricsJson}

Provide:
1. Overall assessment (2-3 sentences)
2. What's working well
3. What needs improvement
4. Specific recommendations for next week (subreddits to focus on, content types to emphasize)
5. Any phase transition readiness assessment

Format as JSON with keys: summary, working_well, needs_improvement, recommendations, phase_assessment
PROMPT;

        $response = Http::withHeaders([
            'x-api-key' => config('reddit.anthropic_api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => config('reddit.strategist_model'),
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->failed()) {
            Log::error('Claude API strategy report failed', ['status' => $response->status()]);

            return ['error' => 'Failed to generate report: ' . $response->status()];
        }

        return json_decode($response->json('content.0.text', '{}'), true) ?? [];
    }
}
```

- [ ] **Step 4: Register API bindings in DomainServiceProvider**

Add to imports and `$bindings` in `app/Providers/DomainServiceProvider.php`:

```php
// Add to imports:
use App\Infrastructure\Reddit\ClaudeAiDrafter;
use App\Infrastructure\Reddit\ClaudeAiStrategist;
use App\Infrastructure\Reddit\RedditApiClient;
use Domain\Reddit\Port\AiDrafterInterface;
use Domain\Reddit\Port\AiStrategistInterface;
use Domain\Reddit\Port\RedditApiInterface;

// Add to $bindings array:
RedditApiInterface::class => RedditApiClient::class,
AiDrafterInterface::class => ClaudeAiDrafter::class,
AiStrategistInterface::class => ClaudeAiStrategist::class,
```

- [ ] **Step 4: Commit**

```bash
git add app/Infrastructure/Reddit/ app/Providers/DomainServiceProvider.php
git commit -m "feat(reddit): add Reddit API client and Claude AI drafter"
```

---

## Task 11: Application Commands

**Files:**
- Create: `app/Application/Command/Reddit/ScoutThreads.php`
- Create: `app/Application/Command/Reddit/DraftResponses.php`
- Create: `app/Application/Command/Reddit/PublishApprovedDrafts.php`
- Create: `app/Application/Command/Reddit/GenerateStrategyReport.php`

- [ ] **Step 1: Write ScoutThreads**

```php
<?php

namespace App\Application\Command\Reddit;

use Domain\Reddit\Entity\RedditThread;
use Domain\Reddit\Port\RedditApiInterface;
use Domain\Reddit\Port\RedditSubredditRepositoryInterface;
use Domain\Reddit\Port\RedditThreadRepositoryInterface;
use Domain\Reddit\ValueObject\ThreadStatus;
use Domain\Reddit\ValueObject\ThreadType;

class ScoutThreads
{
    public function __construct(
        private RedditApiInterface $redditApi,
        private RedditSubredditRepositoryInterface $subredditRepo,
        private RedditThreadRepositoryInterface $threadRepo,
    ) {}

    public function execute(): int
    {
        $subreddits = $this->subredditRepo->findActive();
        $newCount = 0;

        foreach ($subreddits as $subreddit) {
            $keywords = $subreddit->keywordsJson ?? ['reviews', 'feedback', 'reputation'];

            foreach ($keywords as $keyword) {
                $results = $this->redditApi->searchSubreddit($subreddit->name, $keyword);

                foreach ($results as $result) {
                    // Skip if already in DB
                    if ($this->threadRepo->findByRedditId($result['id'])) {
                        continue;
                    }

                    // Skip low-score threads
                    if ($result['score'] < 2) {
                        continue;
                    }

                    // Skip threads older than 24 hours
                    $createdAt = \DateTimeImmutable::createFromFormat('U', (string) $result['created_utc']);
                    $hoursSince = (time() - $result['created_utc']) / 3600;
                    if ($hoursSince > 24) {
                        continue;
                    }

                    $thread = new RedditThread(
                        id: 0,
                        subredditId: $subreddit->id,
                        redditId: $result['id'],
                        title: $result['title'],
                        body: $result['selftext'],
                        author: $result['author'],
                        url: $result['url'],
                        score: $result['score'],
                        numComments: $result['num_comments'],
                        threadType: ThreadType::classify($result['title'], $result['selftext']),
                        status: ThreadStatus::New,
                        discoveredAt: new \DateTimeImmutable(),
                        createdAt: new \DateTimeImmutable(),
                    );

                    $thread = $this->threadRepo->save($thread);
                    $newCount++;
                }

                // Rate limiting: sleep between keyword searches
                usleep(500_000);
            }

            // Rate limiting: sleep between subreddits
            sleep(1);
        }

        return $newCount;
    }
}
```

- [ ] **Step 2: Write DraftResponses**

```php
<?php

namespace App\Application\Command\Reddit;

use Domain\Reddit\Entity\RedditDraft;
use Domain\Reddit\Port\AiDrafterInterface;
use Domain\Reddit\Port\RedditDraftRepositoryInterface;
use Domain\Reddit\Port\RedditSubredditRepositoryInterface;
use Domain\Reddit\Port\RedditThreadRepositoryInterface;
use Domain\Reddit\ValueObject\ContentCategory;
use Domain\Reddit\ValueObject\ContentMixPolicy;
use Domain\Reddit\ValueObject\DraftStatus;
use Domain\Reddit\ValueObject\DraftType;
use Domain\Reddit\ValueObject\PhasePolicy;
use Domain\Reddit\ValueObject\SubredditCadencePolicy;

class DraftResponses
{
    public function __construct(
        private RedditThreadRepositoryInterface $threadRepo,
        private RedditDraftRepositoryInterface $draftRepo,
        private RedditSubredditRepositoryInterface $subredditRepo,
        private AiDrafterInterface $aiDrafter,
    ) {}

    public function execute(): int
    {
        $accountCreatedAt = config('reddit.account_created_at');
        if (! $accountCreatedAt) {
            return 0;
        }

        $phasePolicy = new PhasePolicy(new \DateTimeImmutable($accountCreatedAt));
        if (! $phasePolicy->canDraft()) {
            return 0;
        }

        // Mark stale threads
        $this->threadRepo->markStaleThreads(new \DateTimeImmutable('-24 hours'));

        // Get content mix for ratio enforcement
        $counts = $this->draftRepo->countByContentCategoryBetween(
            new \DateTimeImmutable('-30 days'),
            new \DateTimeImmutable(),
        );
        $mixPolicy = new ContentMixPolicy($counts);

        $threads = $this->threadRepo->findNewThreads(10);
        $draftCount = 0;

        foreach ($threads as $thread) {
            $subreddit = $this->subredditRepo->findById($thread->subredditId);
            if (! $subreddit) {
                continue;
            }

            // Check cadence
            $weekCounts = $this->draftRepo->countPublishedThisWeek($subreddit->id);
            $cadence = new SubredditCadencePolicy(
                $subreddit->maxPostsPerWeek,
                $subreddit->maxCommentsPerWeek,
                $weekCounts['posts'],
                $weekCounts['comments'],
            );

            // Determine draft type: comments for thread replies, posts only in full phase for general topics
            $draftType = DraftType::Comment;

            if (! $cadence->canPublish($draftType)) {
                continue;
            }

            // Determine content category
            $contentCategory = $mixPolicy->suggestCategory();

            // Brand content only for tool recommendation threads when ratio allows
            if ($thread->threadType === \Domain\Reddit\ValueObject\ThreadType::ToolRecommendation && $mixPolicy->canGenerate(ContentCategory::Brand)) {
                $contentCategory = ContentCategory::Brand;
            }

            if (! $mixPolicy->canGenerate($contentCategory)) {
                $contentCategory = ContentCategory::Value;
            }

            $body = $this->aiDrafter->generateDraft(
                $thread,
                $subreddit->name,
                $subreddit->rulesJson,
                $contentCategory,
                $draftType,
            );

            $draft = new RedditDraft(
                id: 0,
                threadId: $thread->id,
                subredditId: $subreddit->id,
                type: $draftType,
                contentCategory: $contentCategory,
                title: null,
                body: $body,
                status: DraftStatus::Pending,
                redditThingId: null,
                publishedAt: null,
                approvedAt: null,
                rejectedAt: null,
                rejectionReason: null,
                redditScore: null,
                redditNumComments: null,
                createdAt: new \DateTimeImmutable(),
            );

            $draft = $this->draftRepo->save($draft);
            $thread->markDrafted();
            $thread = $this->threadRepo->save($thread);

            $draftCount++;
        }

        return $draftCount;
    }
}
```

- [ ] **Step 3: Write PublishApprovedDrafts**

```php
<?php

namespace App\Application\Command\Reddit;

use Domain\Reddit\Port\RedditApiInterface;
use Domain\Reddit\Port\RedditDraftRepositoryInterface;
use Domain\Reddit\Port\RedditSubredditRepositoryInterface;
use Domain\Reddit\Port\RedditThreadRepositoryInterface;
use Domain\Reddit\ValueObject\DraftStatus;
use Domain\Reddit\ValueObject\DraftType;
use Domain\Reddit\ValueObject\PhasePolicy;
use Domain\Reddit\ValueObject\SubredditCadencePolicy;
use Illuminate\Support\Facades\Log;

class PublishApprovedDrafts
{
    public function __construct(
        private RedditDraftRepositoryInterface $draftRepo,
        private RedditThreadRepositoryInterface $threadRepo,
        private RedditSubredditRepositoryInterface $subredditRepo,
        private RedditApiInterface $redditApi,
    ) {}

    public function execute(): int
    {
        $accountCreatedAt = config('reddit.account_created_at');
        if (! $accountCreatedAt) {
            return 0;
        }

        $phasePolicy = new PhasePolicy(new \DateTimeImmutable($accountCreatedAt));
        if (! $phasePolicy->canDraft()) {
            return 0;
        }

        $isDryRun = config('reddit.dry_run');

        $drafts = $this->draftRepo->findByStatus(DraftStatus::Approved->value, 3);
        $publishedCount = 0;

        foreach ($drafts as $draft) {
            $subreddit = $this->subredditRepo->findById($draft->subredditId);
            if (! $subreddit) {
                continue;
            }

            // Check cadence one more time before publishing
            $weekCounts = $this->draftRepo->countPublishedThisWeek($subreddit->id);
            $cadence = new SubredditCadencePolicy(
                $subreddit->maxPostsPerWeek,
                $subreddit->maxCommentsPerWeek,
                $weekCounts['posts'],
                $weekCounts['comments'],
            );

            if (! $cadence->canPublish($draft->type)) {
                continue;
            }

            try {
                if ($isDryRun) {
                    Log::info('Reddit dry run: would publish draft', ['draft_id' => $draft->id, 'type' => $draft->type->value]);
                    $publishedCount++;
                    continue;
                }

                if ($draft->type === DraftType::Comment && $draft->threadId) {
                    $thread = $this->threadRepo->findById($draft->threadId);
                    if (! $thread) {
                        continue;
                    }
                    $thingId = $this->redditApi->submitComment($thread->redditId, $draft->body);
                } else {
                    $thingId = $this->redditApi->submitPost(
                        $subreddit->name,
                        $draft->title ?? '',
                        $draft->body,
                    );
                }

                $draft->markPublished($thingId);
                $this->draftRepo->save($draft);
                $publishedCount++;

                Log::info('Reddit draft published', ['draft_id' => $draft->id, 'thing_id' => $thingId]);

                // Random delay between publishes (2-5 minutes)
                if ($publishedCount < count($drafts)) {
                    sleep(rand(120, 300));
                }
            } catch (\Throwable $e) {
                $draft->markFailed();
                $this->draftRepo->save($draft);
                Log::error('Reddit publish failed', ['draft_id' => $draft->id, 'error' => $e->getMessage()]);
            }
        }

        return $publishedCount;
    }
}
```

- [ ] **Step 4: Write GenerateStrategyReport**

```php
<?php

namespace App\Application\Command\Reddit;

use Domain\Reddit\Entity\RedditStrategyReport;
use Domain\Reddit\Port\AiStrategistInterface;
use Domain\Reddit\Port\RedditDraftRepositoryInterface;
use Domain\Reddit\Port\RedditStrategyReportRepositoryInterface;
use Domain\Reddit\ValueObject\DraftStatus;
use Domain\Reddit\ValueObject\PhasePolicy;

class GenerateStrategyReport
{
    public function __construct(
        private RedditDraftRepositoryInterface $draftRepo,
        private RedditStrategyReportRepositoryInterface $reportRepo,
        private AiStrategistInterface $aiStrategist,
    ) {}

    public function execute(): void
    {
        $periodEnd = new \DateTimeImmutable();
        $periodStart = new \DateTimeImmutable('-7 days');

        // Gather metrics
        $contentRatio = $this->draftRepo->countByContentCategoryBetween($periodStart, $periodEnd);

        $published = $this->draftRepo->findByStatus(DraftStatus::Published->value, 100);
        $thisWeekPublished = array_filter($published, fn ($d) => $d->createdAt >= $periodStart);

        $topPerforming = array_filter($thisWeekPublished, fn ($d) => $d->redditScore !== null);
        usort($topPerforming, fn ($a, $b) => ($b->redditScore ?? 0) <=> ($a->redditScore ?? 0));
        $topPerforming = array_slice($topPerforming, 0, 5);

        $accountCreatedAt = config('reddit.account_created_at');
        $phasePolicy = $accountCreatedAt ? new PhasePolicy(new \DateTimeImmutable($accountCreatedAt)) : null;

        $metricsContext = [
            'period' => $periodStart->format('Y-m-d') . ' to ' . $periodEnd->format('Y-m-d'),
            'content_ratio' => $contentRatio,
            'published_count' => count($thisWeekPublished),
            'phase' => $phasePolicy?->currentPhase()->value ?? 'unknown',
            'account_age_days' => $phasePolicy?->accountAgeDays() ?? 0,
            'top_performing' => array_map(fn ($d) => [
                'body_preview' => substr($d->body, 0, 100),
                'score' => $d->redditScore,
                'comments' => $d->redditNumComments,
                'category' => $d->contentCategory->value,
            ], $topPerforming),
        ];

        $reportContent = $this->aiStrategist->analyzeWeeklyMetrics($metricsContext);

        $report = new RedditStrategyReport(
            id: 0,
            periodStart: $periodStart,
            periodEnd: $periodEnd,
            reportJson: $reportContent,
            recommendationsJson: $reportContent['recommendations'] ?? [],
            contentRatioJson: $contentRatio,
            topPerformingJson: array_map(fn ($d) => [
                'id' => $d->id,
                'body_preview' => substr($d->body, 0, 200),
                'score' => $d->redditScore,
                'category' => $d->contentCategory->value,
            ], $topPerforming),
            createdAt: new \DateTimeImmutable(),
        );

        $this->reportRepo->save($report);
    }
}
```

- [ ] **Step 5: Commit**

```bash
git add app/Application/Command/Reddit/
git commit -m "feat(reddit): add application commands (scout, draft, publish, strategist)"
```

---

## Task 12: Artisan Commands + Schedule

**Files:**
- Create: `app/Console/Commands/Reddit/ScoutRedditThreads.php`
- Create: `app/Console/Commands/Reddit/DraftRedditResponses.php`
- Create: `app/Console/Commands/Reddit/PublishRedditDrafts.php`
- Create: `app/Console/Commands/Reddit/RunRedditStrategist.php`
- Modify: `routes/console.php`

- [ ] **Step 1: Write ScoutRedditThreads artisan command**

```php
<?php

namespace App\Console\Commands\Reddit;

use App\Application\Command\Reddit\ScoutThreads;
use Illuminate\Console\Command;

class ScoutRedditThreads extends Command
{
    protected $signature = 'reddit:scout';

    protected $description = 'Scout Reddit for relevant threads across target subreddits';

    public function handle(ScoutThreads $command): int
    {
        if (! config('reddit.enabled')) {
            $this->info('Reddit agent is disabled.');

            return self::SUCCESS;
        }

        $this->info('Scouting Reddit for new threads...');

        $count = $command->execute();

        $this->info("Found {$count} new threads.");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 2: Write DraftRedditResponses artisan command**

```php
<?php

namespace App\Console\Commands\Reddit;

use App\Application\Command\Reddit\DraftResponses;
use Illuminate\Console\Command;

class DraftRedditResponses extends Command
{
    protected $signature = 'reddit:draft';

    protected $description = 'Generate AI drafts for new Reddit threads';

    public function handle(DraftResponses $command): int
    {
        if (! config('reddit.enabled')) {
            $this->info('Reddit agent is disabled.');

            return self::SUCCESS;
        }

        $this->info('Generating drafts...');

        $count = $command->execute();

        $this->info("Generated {$count} drafts.");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 3: Write PublishRedditDrafts artisan command**

```php
<?php

namespace App\Console\Commands\Reddit;

use App\Application\Command\Reddit\PublishApprovedDrafts;
use Illuminate\Console\Command;

class PublishRedditDrafts extends Command
{
    protected $signature = 'reddit:publish';

    protected $description = 'Publish approved Reddit drafts';

    public function handle(PublishApprovedDrafts $command): int
    {
        if (! config('reddit.enabled')) {
            $this->info('Reddit agent is disabled.');

            return self::SUCCESS;
        }

        $this->info('Publishing approved drafts...');

        $count = $command->execute();

        $this->info("Published {$count} drafts.");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Write RunRedditStrategist artisan command**

```php
<?php

namespace App\Console\Commands\Reddit;

use App\Application\Command\Reddit\GenerateStrategyReport;
use Illuminate\Console\Command;

class RunRedditStrategist extends Command
{
    protected $signature = 'reddit:strategist';

    protected $description = 'Generate weekly Reddit strategy report';

    public function handle(GenerateStrategyReport $command): int
    {
        if (! config('reddit.enabled')) {
            $this->info('Reddit agent is disabled.');

            return self::SUCCESS;
        }

        $this->info('Generating strategy report...');

        $command->execute();

        $this->info('Strategy report generated.');

        return self::SUCCESS;
    }
}
```

- [ ] **Step 5: Add schedule entries to routes/console.php**

Add after the existing outreach schedule entries:

```php
// Reddit marketing agent
Schedule::command('reddit:scout')->everyTwoHours()->withoutOverlapping();
Schedule::command('reddit:draft')->dailyAt('07:00')->withoutOverlapping();
Schedule::command('reddit:publish')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('reddit:strategist')->weeklyOn(0, '10:00')->withoutOverlapping();

// Reddit data retention cleanup (monthly)
Schedule::call(function () {
    app(\Domain\Reddit\Port\RedditThreadRepositoryInterface::class)
        ->purgeOlderThan(new \DateTimeImmutable('-30 days'), [\Domain\Reddit\ValueObject\ThreadStatus::Skipped, \Domain\Reddit\ValueObject\ThreadStatus::Stale]);
    app(\Domain\Reddit\Port\RedditDraftRepositoryInterface::class)
        ->purgeOlderThan(new \DateTimeImmutable('-90 days'), [\Domain\Reddit\ValueObject\DraftStatus::Rejected]);
})->monthly()->withoutOverlapping();
```

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/Reddit/ routes/console.php
git commit -m "feat(reddit): add artisan commands and scheduler entries"
```

---

## Task 13: Admin Routes + Dashboard Controller

**Files:**
- Create: `app/Http/Controllers/Admin/RedditDashboardController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write RedditDashboardController**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use App\Infrastructure\Persistence\Eloquent\RedditStrategyReportModel;
use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use Domain\Reddit\ValueObject\PhasePolicy;

class RedditDashboardController extends Controller
{
    public function index()
    {
        $accountCreatedAt = config('reddit.account_created_at');
        $phasePolicy = $accountCreatedAt ? new PhasePolicy(new \DateTimeImmutable($accountCreatedAt)) : null;

        $stats = RedditDraftModel::query()
            ->selectRaw('COUNT(*) as total_drafts')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'pending') as pending")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'approved') as approved")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'published') as published")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'rejected') as rejected")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'failed') as failed")
            ->first();

        $threadsThisWeek = RedditThreadModel::where('created_at', '>=', now()->startOfWeek())->count();

        // Content ratio for the last 30 days
        $ratioRaw = RedditDraftModel::where('created_at', '>=', now()->subDays(30))
            ->whereIn('status', ['pending', 'approved', 'published'])
            ->selectRaw('content_category, COUNT(*) as count')
            ->groupBy('content_category')
            ->pluck('count', 'content_category')
            ->toArray();

        $contentRatio = [
            'value' => $ratioRaw['value'] ?? 0,
            'discussion' => $ratioRaw['discussion'] ?? 0,
            'brand' => $ratioRaw['brand'] ?? 0,
        ];

        $latestReport = RedditStrategyReportModel::orderByDesc('period_end')->first();

        $recentDrafts = RedditDraftModel::with('subreddit', 'thread')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.reddit.dashboard', compact(
            'phasePolicy',
            'stats',
            'threadsThisWeek',
            'contentRatio',
            'latestReport',
            'recentDrafts',
        ));
    }
}
```

- [ ] **Step 2: Add Reddit admin routes to routes/web.php**

Add inside the existing admin route group:

```php
// Reddit marketing agent
Route::get('/reddit', [\App\Http\Controllers\Admin\RedditDashboardController::class, 'index'])->name('reddit.dashboard');
Route::get('/reddit/drafts', [\App\Http\Controllers\Admin\RedditDraftController::class, 'index'])->name('reddit.drafts.index');
Route::get('/reddit/drafts/{id}', [\App\Http\Controllers\Admin\RedditDraftController::class, 'show'])->name('reddit.drafts.show');
Route::post('/reddit/drafts/{id}/approve', [\App\Http\Controllers\Admin\RedditDraftController::class, 'approve'])->name('reddit.drafts.approve');
Route::post('/reddit/drafts/{id}/reject', [\App\Http\Controllers\Admin\RedditDraftController::class, 'reject'])->name('reddit.drafts.reject');
Route::post('/reddit/drafts/{id}/retry', [\App\Http\Controllers\Admin\RedditDraftController::class, 'retry'])->name('reddit.drafts.retry');
Route::patch('/reddit/drafts/{id}', [\App\Http\Controllers\Admin\RedditDraftController::class, 'update'])->name('reddit.drafts.update');
Route::get('/reddit/subreddits', [\App\Http\Controllers\Admin\RedditSubredditController::class, 'index'])->name('reddit.subreddits.index');
Route::patch('/reddit/subreddits/{id}', [\App\Http\Controllers\Admin\RedditSubredditController::class, 'update'])->name('reddit.subreddits.update');
Route::get('/reddit/reports/{id}', [\App\Http\Controllers\Admin\RedditStrategyReportController::class, 'show'])->name('reddit.reports.show');
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Admin/RedditDashboardController.php routes/web.php
git commit -m "feat(reddit): add admin dashboard controller and routes"
```

---

## Task 14: Draft Controller

**Files:**
- Create: `app/Http/Controllers/Admin/RedditDraftController.php`

- [ ] **Step 1: Write RedditDraftController**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use Illuminate\Http\Request;

class RedditDraftController extends Controller
{
    public function index(Request $request)
    {
        $query = RedditDraftModel::with('subreddit', 'thread');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('subreddit')) {
            $query->where('reddit_subreddit_id', $request->subreddit);
        }

        if ($request->filled('content_category')) {
            $query->where('content_category', $request->content_category);
        }

        $drafts = $query->orderByDesc('created_at')->paginate(25);

        return view('admin.reddit.drafts.index', compact('drafts'));
    }

    public function show(int $id)
    {
        $draft = RedditDraftModel::with('subreddit', 'thread')->findOrFail($id);

        return view('admin.reddit.drafts.show', compact('draft'));
    }

    public function approve(int $id)
    {
        $draft = RedditDraftModel::findOrFail($id);
        $draft->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Draft approved.');
    }

    public function reject(Request $request, int $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $draft = RedditDraftModel::findOrFail($id);
        $draft->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $request->input('reason'),
        ]);

        return back()->with('success', 'Draft rejected.');
    }

    public function retry(int $id)
    {
        $draft = RedditDraftModel::findOrFail($id);
        $draft->update(['status' => 'approved']);

        return back()->with('success', 'Draft queued for retry.');
    }

    public function update(Request $request, int $id)
    {
        $request->validate(['body' => 'required|string|max:5000']);

        $draft = RedditDraftModel::findOrFail($id);
        $draft->update(['body' => $request->input('body')]);

        return back()->with('success', 'Draft updated.');
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/Admin/RedditDraftController.php
git commit -m "feat(reddit): add draft controller with approve/reject/retry"
```

---

## Task 15: Subreddit + Report Controllers

**Files:**
- Create: `app/Http/Controllers/Admin/RedditSubredditController.php`
- Create: `app/Http/Controllers/Admin/RedditStrategyReportController.php`

- [ ] **Step 1: Write RedditSubredditController**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use Illuminate\Http\Request;

class RedditSubredditController extends Controller
{
    public function index()
    {
        $subreddits = RedditSubredditModel::orderBy('tier')
            ->orderBy('name')
            ->withCount(['drafts as published_this_week' => function ($q) {
                $q->where('status', 'published')->where('published_at', '>=', now()->startOfWeek());
            }])
            ->get();

        return view('admin.reddit.subreddits.index', compact('subreddits'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'max_posts_per_week' => 'required|integer|min:0|max:20',
            'max_comments_per_week' => 'required|integer|min:0|max:50',
            'is_active' => 'boolean',
        ]);

        $subreddit = RedditSubredditModel::findOrFail($id);
        $subreddit->update($request->only('max_posts_per_week', 'max_comments_per_week', 'is_active'));

        return back()->with('success', "r/{$subreddit->name} updated.");
    }
}
```

- [ ] **Step 2: Write RedditStrategyReportController**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\RedditStrategyReportModel;

class RedditStrategyReportController extends Controller
{
    public function show(int $id)
    {
        $report = RedditStrategyReportModel::findOrFail($id);

        return view('admin.reddit.reports.show', compact('report'));
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Admin/RedditSubredditController.php app/Http/Controllers/Admin/RedditStrategyReportController.php
git commit -m "feat(reddit): add subreddit and report controllers"
```

---

## Task 16: Dashboard View

**Files:**
- Create: `resources/views/admin/reddit/dashboard.blade.php`

- [ ] **Step 1: Write dashboard view**

Build this view following the same `<x-app-layout>` pattern used in `resources/views/admin/outreach/index.blade.php`. Include:

- Phase badge showing current phase (Lurk/Comment/Full) with account age days
- Stats cards: threads this week, pending drafts, published, failed
- Content ratio display (value/discussion/brand counts vs targets)
- Recent drafts table with status, subreddit, preview, and link to detail
- Latest strategy report summary (if exists)
- "Review Drafts" and "Manage Subreddits" navigation buttons

Use the existing Outreach dashboard as the style reference -- same Tailwind classes, card patterns, and layout structure.

- [ ] **Step 2: Commit**

```bash
git add resources/views/admin/reddit/dashboard.blade.php
git commit -m "feat(reddit): add admin dashboard view"
```

---

## Task 17: Draft Views

**Files:**
- Create: `resources/views/admin/reddit/drafts/index.blade.php`
- Create: `resources/views/admin/reddit/drafts/show.blade.php`

- [ ] **Step 1: Write drafts index view**

Table with columns: Status badge, Subreddit, Thread title (linked), Content category badge, Body preview (first 100 chars), Created date, Action buttons (Approve/Reject/Retry based on status).

Include filter dropdowns for status, subreddit, and content category. Pagination at bottom.

Follow the same `<x-app-layout>` pattern as the outreach dashboard.

- [ ] **Step 2: Write draft show view**

- Thread context section: title, body, author, score, subreddit, reddit link
- Draft body in an editable `<textarea>` within a form
- Content category and draft type badges
- Subreddit rules reminder (if rules_json exists on subreddit)
- Action buttons: Save Changes, Approve, Reject (with optional reason input), Retry (if failed)
- Status history: created, approved_at, rejected_at, published_at timestamps

- [ ] **Step 3: Commit**

```bash
git add resources/views/admin/reddit/drafts/
git commit -m "feat(reddit): add draft list and detail views"
```

---

## Task 18: Subreddit + Report Views

**Files:**
- Create: `resources/views/admin/reddit/subreddits/index.blade.php`
- Create: `resources/views/admin/reddit/reports/show.blade.php`

- [ ] **Step 1: Write subreddits index view**

Table with columns: Name (linked to reddit.com), Tier badge, Max posts/week, Max comments/week, Published this week, Active toggle.

Each row has an inline form (PATCH) to update cadence limits and active status.

- [ ] **Step 2: Write report show view**

Display the strategy report JSON in a readable format:
- Period dates
- Summary section
- Content ratio display (actual vs target)
- Top performing drafts
- Recommendations list
- Phase assessment

- [ ] **Step 3: Commit**

```bash
git add resources/views/admin/reddit/subreddits/ resources/views/admin/reddit/reports/
git commit -m "feat(reddit): add subreddit management and report views"
```

---

## Task 19: Feature Tests -- Admin Dashboard + Draft Controller

**Files:**
- Create: `tests/Feature/Reddit/RedditDashboardTest.php`
- Create: `tests/Feature/Reddit/RedditDraftControllerTest.php`

- [ ] **Step 1: Write RedditDashboardTest**

```php
<?php

namespace Tests\Feature\Reddit;

use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedditDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = \App\Infrastructure\Persistence\Eloquent\TenantModel::create([
            'name' => 'Test',
            'slug' => 'test-' . uniqid(),
            'trial_ends_at' => now()->addDays(14),
        ]);
        $this->admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_admin' => true,
        ]);
    }

    public function test_dashboard_loads_for_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reddit');

        $response->assertStatus(200);
    }

    public function test_dashboard_shows_stats(): void
    {
        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
        ]);

        $thread = RedditThreadModel::create([
            'reddit_subreddit_id' => $sub->id,
            'reddit_id' => 't3_test1',
            'title' => 'How to get reviews',
            'author' => 'testuser',
            'url' => 'https://reddit.com/r/test/1',
            'thread_type' => 'how_to_get_reviews',
            'status' => 'new',
            'discovered_at' => now(),
        ]);

        RedditDraftModel::create([
            'reddit_thread_id' => $thread->id,
            'reddit_subreddit_id' => $sub->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Test draft body',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/reddit');

        $response->assertStatus(200);
        $response->assertSee('Test draft body');
    }

    public function test_non_admin_cannot_access(): void
    {
        $tenant = \App\Infrastructure\Persistence\Eloquent\TenantModel::create([
            'name' => 'Other',
            'slug' => 'other-' . uniqid(),
        ]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get('/admin/reddit');

        $response->assertStatus(403);
    }
}
```

- [ ] **Step 2: Run dashboard tests**

Run: `./vendor/bin/sail artisan test --filter=RedditDashboardTest`
Expected: PASS (3 tests)

- [ ] **Step 3: Write RedditDraftControllerTest**

```php
<?php

namespace Tests\Feature\Reddit;

use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedditDraftControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private RedditSubredditModel $subreddit;
    private RedditThreadModel $thread;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = \App\Infrastructure\Persistence\Eloquent\TenantModel::create([
            'name' => 'Test',
            'slug' => 'test-' . uniqid(),
            'trial_ends_at' => now()->addDays(14),
        ]);
        $this->admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_admin' => true,
        ]);

        $this->subreddit = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
        ]);

        $this->thread = RedditThreadModel::create([
            'reddit_subreddit_id' => $this->subreddit->id,
            'reddit_id' => 't3_abc123',
            'title' => 'How to get reviews',
            'author' => 'testuser',
            'url' => 'https://reddit.com/r/smallbusiness/abc123',
            'thread_type' => 'how_to_get_reviews',
            'status' => 'drafted',
            'discovered_at' => now(),
        ]);
    }

    public function test_drafts_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reddit/drafts');

        $response->assertStatus(200);
    }

    public function test_approve_draft(): void
    {
        $draft = RedditDraftModel::create([
            'reddit_thread_id' => $this->thread->id,
            'reddit_subreddit_id' => $this->subreddit->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Great advice here...',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/reddit/drafts/{$draft->id}/approve");

        $response->assertRedirect();
        $this->assertDatabaseHas('reddit_drafts', ['id' => $draft->id, 'status' => 'approved']);
    }

    public function test_reject_draft(): void
    {
        $draft = RedditDraftModel::create([
            'reddit_thread_id' => $this->thread->id,
            'reddit_subreddit_id' => $this->subreddit->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Draft to reject',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/reddit/drafts/{$draft->id}/reject", [
            'reason' => 'Too promotional',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reddit_drafts', [
            'id' => $draft->id,
            'status' => 'rejected',
            'rejection_reason' => 'Too promotional',
        ]);
    }

    public function test_retry_failed_draft(): void
    {
        $draft = RedditDraftModel::create([
            'reddit_thread_id' => $this->thread->id,
            'reddit_subreddit_id' => $this->subreddit->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Draft that failed',
            'status' => 'failed',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/reddit/drafts/{$draft->id}/retry");

        $response->assertRedirect();
        $this->assertDatabaseHas('reddit_drafts', ['id' => $draft->id, 'status' => 'approved']);
    }

    public function test_update_draft_body(): void
    {
        $draft = RedditDraftModel::create([
            'reddit_thread_id' => $this->thread->id,
            'reddit_subreddit_id' => $this->subreddit->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Original body',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->patch("/admin/reddit/drafts/{$draft->id}", [
            'body' => 'Updated body text',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reddit_drafts', ['id' => $draft->id, 'body' => 'Updated body text']);
    }
}
```

- [ ] **Step 4: Run draft controller tests**

Run: `./vendor/bin/sail artisan test --filter=RedditDraftControllerTest`
Expected: PASS (5 tests)

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/Reddit/
git commit -m "test(reddit): add feature tests for dashboard and draft controller"
```

---

## Task 20: Feature Tests -- Application Commands

**Files:**
- Create: `tests/Feature/Reddit/ScoutThreadsTest.php`
- Create: `tests/Feature/Reddit/DraftResponsesTest.php`
- Create: `tests/Feature/Reddit/PublishApprovedDraftsTest.php`

- [ ] **Step 1: Write ScoutThreadsTest**

```php
<?php

namespace Tests\Feature\Reddit;

use App\Application\Command\Reddit\ScoutThreads;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use Domain\Reddit\Port\RedditApiInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoutThreadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_scouts_and_saves_threads(): void
    {
        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
            'keywords_json' => ['reviews'],
        ]);

        $mockApi = $this->mock(RedditApiInterface::class);
        $mockApi->shouldReceive('searchSubreddit')
            ->andReturn([
                [
                    'id' => 't3_abc123',
                    'title' => 'How to get more Google reviews?',
                    'selftext' => 'Looking for advice...',
                    'author' => 'testuser',
                    'url' => 'https://reddit.com/r/smallbusiness/abc123',
                    'score' => 5,
                    'num_comments' => 3,
                    'created_utc' => time() - 3600,
                ],
            ]);

        $command = app(ScoutThreads::class);
        $count = $command->execute();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('reddit_threads', [
            'reddit_id' => 't3_abc123',
            'thread_type' => 'how_to_get_reviews',
            'status' => 'new',
        ]);
    }

    public function test_skips_low_score_threads(): void
    {
        RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
            'keywords_json' => ['reviews'],
        ]);

        $mockApi = $this->mock(RedditApiInterface::class);
        $mockApi->shouldReceive('searchSubreddit')
            ->andReturn([
                [
                    'id' => 't3_low',
                    'title' => 'Some thread',
                    'selftext' => null,
                    'author' => 'user',
                    'url' => 'https://reddit.com/r/test/low',
                    'score' => 1,
                    'num_comments' => 0,
                    'created_utc' => time() - 3600,
                ],
            ]);

        $command = app(ScoutThreads::class);
        $count = $command->execute();

        $this->assertSame(0, $count);
    }

    public function test_skips_duplicate_threads(): void
    {
        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
            'keywords_json' => ['reviews'],
        ]);

        \App\Infrastructure\Persistence\Eloquent\RedditThreadModel::create([
            'reddit_subreddit_id' => $sub->id,
            'reddit_id' => 't3_existing',
            'title' => 'Existing thread',
            'author' => 'user',
            'url' => 'https://reddit.com/r/test/existing',
            'thread_type' => 'general',
            'status' => 'new',
            'discovered_at' => now(),
        ]);

        $mockApi = $this->mock(RedditApiInterface::class);
        $mockApi->shouldReceive('searchSubreddit')
            ->andReturn([
                [
                    'id' => 't3_existing',
                    'title' => 'Existing thread',
                    'selftext' => null,
                    'author' => 'user',
                    'url' => 'https://reddit.com/r/test/existing',
                    'score' => 5,
                    'num_comments' => 2,
                    'created_utc' => time() - 3600,
                ],
            ]);

        $command = app(ScoutThreads::class);
        $count = $command->execute();

        $this->assertSame(0, $count);
    }
}
```

- [ ] **Step 2: Write DraftResponsesTest**

```php
<?php

namespace Tests\Feature\Reddit;

use App\Application\Command\Reddit\DraftResponses;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use Domain\Reddit\Port\AiDrafterInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DraftResponsesTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_drafts_for_new_threads(): void
    {
        config(['reddit.account_created_at' => now()->subDays(20)->format('Y-m-d')]);

        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
        ]);

        RedditThreadModel::create([
            'reddit_subreddit_id' => $sub->id,
            'reddit_id' => 't3_test1',
            'title' => 'How to get more reviews',
            'author' => 'testuser',
            'url' => 'https://reddit.com/r/test/1',
            'thread_type' => 'how_to_get_reviews',
            'status' => 'new',
            'discovered_at' => now(),
        ]);

        $mockDrafter = $this->mock(AiDrafterInterface::class);
        $mockDrafter->shouldReceive('generateDraft')
            ->once()
            ->andReturn('Here is my helpful response about getting reviews...');

        $command = app(DraftResponses::class);
        $count = $command->execute();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('reddit_drafts', [
            'status' => 'pending',
            'type' => 'comment',
            'body' => 'Here is my helpful response about getting reviews...',
        ]);
        $this->assertDatabaseHas('reddit_threads', [
            'reddit_id' => 't3_test1',
            'status' => 'drafted',
        ]);
    }

    public function test_skips_during_lurk_phase(): void
    {
        config(['reddit.account_created_at' => now()->subDays(5)->format('Y-m-d')]);

        $command = app(DraftResponses::class);
        $count = $command->execute();

        $this->assertSame(0, $count);
    }
}
```

- [ ] **Step 3: Write PublishApprovedDraftsTest**

```php
<?php

namespace Tests\Feature\Reddit;

use App\Application\Command\Reddit\PublishApprovedDrafts;
use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use Domain\Reddit\Port\RedditApiInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishApprovedDraftsTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishes_approved_drafts(): void
    {
        config(['reddit.account_created_at' => now()->subDays(20)->format('Y-m-d')]);

        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
        ]);

        $thread = RedditThreadModel::create([
            'reddit_subreddit_id' => $sub->id,
            'reddit_id' => 't3_test1',
            'title' => 'How to get reviews',
            'author' => 'testuser',
            'url' => 'https://reddit.com/r/test/1',
            'thread_type' => 'how_to_get_reviews',
            'status' => 'drafted',
            'discovered_at' => now(),
        ]);

        $draft = RedditDraftModel::create([
            'reddit_thread_id' => $thread->id,
            'reddit_subreddit_id' => $sub->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Helpful comment here',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $mockApi = $this->mock(RedditApiInterface::class);
        $mockApi->shouldReceive('submitComment')
            ->once()
            ->with('t3_test1', 'Helpful comment here')
            ->andReturn('t1_published123');

        $command = app(PublishApprovedDrafts::class);
        $count = $command->execute();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('reddit_drafts', [
            'id' => $draft->id,
            'status' => 'published',
            'reddit_thing_id' => 't1_published123',
        ]);
    }

    public function test_marks_draft_failed_on_api_error(): void
    {
        config(['reddit.account_created_at' => now()->subDays(20)->format('Y-m-d')]);

        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
        ]);

        $thread = RedditThreadModel::create([
            'reddit_subreddit_id' => $sub->id,
            'reddit_id' => 't3_fail',
            'title' => 'Thread',
            'author' => 'user',
            'url' => 'https://reddit.com/r/test/fail',
            'thread_type' => 'general',
            'status' => 'drafted',
            'discovered_at' => now(),
        ]);

        $draft = RedditDraftModel::create([
            'reddit_thread_id' => $thread->id,
            'reddit_subreddit_id' => $sub->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Will fail',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $mockApi = $this->mock(RedditApiInterface::class);
        $mockApi->shouldReceive('submitComment')
            ->andThrow(new \RuntimeException('403 Forbidden'));

        $command = app(PublishApprovedDrafts::class);
        $count = $command->execute();

        $this->assertSame(0, $count);
        $this->assertDatabaseHas('reddit_drafts', [
            'id' => $draft->id,
            'status' => 'failed',
        ]);
    }
}
```

- [ ] **Step 4: Run all feature tests**

Run: `./vendor/bin/sail artisan test --filter=Reddit`
Expected: PASS (all Reddit tests)

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/Reddit/
git commit -m "test(reddit): add feature tests for application commands"
```

---

## Task 21: Run Full Test Suite + Format

- [ ] **Step 1: Run full test suite**

Run: `./vendor/bin/sail composer test`
Expected: All tests pass (existing + new Reddit tests).

- [ ] **Step 2: Run code formatter**

Run: `./vendor/bin/sail exec laravel.test ./vendor/bin/pint`
Expected: Code formatted.

- [ ] **Step 3: Commit any formatting changes**

```bash
git add -A
git commit -m "chore(reddit): apply pint formatting"
```

---

## Task 22: End-to-End Dry Run Verification

- [ ] **Step 1: Run migration fresh and seed**

Run: `./vendor/bin/sail artisan migrate:fresh --seed` then `./vendor/bin/sail artisan db:seed --class=RedditSubredditSeeder`

- [ ] **Step 2: Run scout in dry mode**

Set `REDDIT_ENABLED=true` and `REDDIT_DRY_RUN=true` in `.env`.

Run: `./vendor/bin/sail artisan reddit:scout`
Expected: Command runs without errors (may find 0 threads without valid API credentials).

- [ ] **Step 3: Verify dashboard loads**

Visit `/admin/reddit` in browser.
Expected: Dashboard loads with phase indicator, empty stats, no errors.

- [ ] **Step 4: Verify all admin pages load**

Visit `/admin/reddit/drafts`, `/admin/reddit/subreddits`.
Expected: All pages load with empty/seeded data, no errors.
