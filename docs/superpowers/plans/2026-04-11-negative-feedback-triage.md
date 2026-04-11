# Negative Feedback Triage Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** When negative feedback is submitted, AI (Claude Haiku) extracts category, urgency, and a suggested response - shown in the owner's email notification and the dashboard feedback list.

**Architecture:** Event listener chain - `NegativeFeedbackReceived` triggers a new `TriageNegativeFeedback` queued listener that calls Claude, stores the result, and dispatches `FeedbackTriaged`. The existing `NotifyOwnerOnNegativeFeedback` moves to listen on `FeedbackTriaged` and sends an enriched email (or raw fallback if LLM failed). Dashboard feedback list eager-loads triage data.

**Tech Stack:** Laravel 12, PHP 8.2, Anthropic Messages API (Haiku), Blade/Alpine.js, PHPUnit

**Spec:** `docs/superpowers/specs/2026-04-11-negative-feedback-triage-design.md`

---

## File Structure

### New Files (Domain)
- `src/Domain/Feedback/ValueObject/TriageCategory.php` - backed string enum (7 categories)
- `src/Domain/Feedback/ValueObject/TriageUrgency.php` - backed string enum (3 levels)
- `src/Domain/Feedback/Entity/FeedbackTriage.php` - triage result entity
- `src/Domain/Feedback/Event/FeedbackTriaged.php` - domain event after triage completes
- `src/Domain/Feedback/Port/FeedbackTriageServiceInterface.php` - LLM port
- `src/Domain/Feedback/Port/FeedbackTriageRepositoryInterface.php` - persistence port

### New Files (Infrastructure)
- `app/Infrastructure/Ai/ClaudeFeedbackTriageService.php` - Anthropic API adapter
- `app/Infrastructure/Persistence/Eloquent/FeedbackTriageModel.php` - Eloquent model
- `app/Infrastructure/Persistence/Repository/EloquentFeedbackTriageRepository.php` - repository impl

### New Files (Listeners)
- `app/Listeners/TriageNegativeFeedback.php` - queued listener on NegativeFeedbackReceived

### New Files (Views)
- `resources/views/mail/negative-feedback.blade.php` - enriched email template

### New Files (Database)
- `database/migrations/YYYY_MM_DD_HHMMSS_create_feedback_triages_table.php`

### New Files (Tests)
- `tests/Unit/Domain/Feedback/TriageCategoryTest.php`
- `tests/Unit/Domain/Feedback/TriageUrgencyTest.php`
- `tests/Unit/Domain/Feedback/FeedbackTriageEntityTest.php`
- `tests/Feature/Feedback/TriageNegativeFeedbackListenerTest.php`
- `tests/Feature/Feedback/NotifyOwnerOnNegativeFeedbackTest.php`
- `tests/Feature/Feedback/FeedbackTriageRepositoryTest.php`
- `tests/Feature/Feedback/GetFeedbackListWithTriageTest.php`
- `tests/Feature/Feedback/FeedbackIndexViewTest.php`

### Modified Files
- `config/services.php` - add `anthropic` key
- `app/Providers/DomainServiceProvider.php` - add triage bindings
- `app/Providers/AppServiceProvider.php` - rewire event listeners
- `app/Listeners/NotifyOwnerOnNegativeFeedback.php` - listen on FeedbackTriaged, use Blade template
- `app/Application/Query/GetFeedbackList.php` - return triage data
- `app/Infrastructure/Persistence/Repository/EloquentFeedbackRepository.php` - eager-load triage
- `app/Infrastructure/Persistence/Eloquent/FeedbackModel.php` - add triage relationship
- `resources/views/feedback/index.blade.php` - show triage cards

---

### Task 1: Domain Value Objects + Entity

**Files:**
- Create: `src/Domain/Feedback/ValueObject/TriageCategory.php`
- Create: `src/Domain/Feedback/ValueObject/TriageUrgency.php`
- Create: `src/Domain/Feedback/Entity/FeedbackTriage.php`
- Test: `tests/Unit/Domain/Feedback/TriageCategoryTest.php`
- Test: `tests/Unit/Domain/Feedback/TriageUrgencyTest.php`
- Test: `tests/Unit/Domain/Feedback/FeedbackTriageEntityTest.php`

- [ ] **Step 1: Write TriageCategory and TriageUrgency tests**

Create `tests/Unit/Domain/Feedback/TriageCategoryTest.php`:

```php
<?php

namespace Tests\Unit\Domain\Feedback;

use Domain\Feedback\ValueObject\TriageCategory;
use PHPUnit\Framework\TestCase;

class TriageCategoryTest extends TestCase
{
    public function test_has_all_expected_cases(): void
    {
        $expected = ['staff', 'wait_time', 'product_quality', 'pricing', 'environment', 'communication', 'other'];

        $actual = array_map(fn (TriageCategory $c) => $c->value, TriageCategory::cases());

        $this->assertSame($expected, $actual);
    }

    public function test_can_be_created_from_string(): void
    {
        $this->assertSame(TriageCategory::Staff, TriageCategory::from('staff'));
        $this->assertSame(TriageCategory::Other, TriageCategory::from('other'));
    }

    public function test_try_from_returns_null_for_invalid(): void
    {
        $this->assertNull(TriageCategory::tryFrom('invalid'));
    }
}
```

Create `tests/Unit/Domain/Feedback/TriageUrgencyTest.php`:

```php
<?php

namespace Tests\Unit\Domain\Feedback;

use Domain\Feedback\ValueObject\TriageUrgency;
use PHPUnit\Framework\TestCase;

class TriageUrgencyTest extends TestCase
{
    public function test_has_all_expected_cases(): void
    {
        $expected = ['low', 'medium', 'high'];

        $actual = array_map(fn (TriageUrgency $u) => $u->value, TriageUrgency::cases());

        $this->assertSame($expected, $actual);
    }

    public function test_can_be_created_from_string(): void
    {
        $this->assertSame(TriageUrgency::High, TriageUrgency::from('high'));
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/sail artisan test --filter=TriageCategoryTest --filter=TriageUrgencyTest`
Expected: FAIL - classes not found

- [ ] **Step 3: Implement TriageCategory and TriageUrgency**

Create `src/Domain/Feedback/ValueObject/TriageCategory.php`:

```php
<?php

declare(strict_types=1);

namespace Domain\Feedback\ValueObject;

enum TriageCategory: string
{
    case Staff = 'staff';
    case WaitTime = 'wait_time';
    case ProductQuality = 'product_quality';
    case Pricing = 'pricing';
    case Environment = 'environment';
    case Communication = 'communication';
    case Other = 'other';
}
```

Create `src/Domain/Feedback/ValueObject/TriageUrgency.php`:

```php
<?php

declare(strict_types=1);

namespace Domain\Feedback\ValueObject;

enum TriageUrgency: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `./vendor/bin/sail artisan test --filter=TriageCategoryTest --filter=TriageUrgencyTest`
Expected: PASS (5 tests)

- [ ] **Step 5: Write FeedbackTriage entity test**

Create `tests/Unit/Domain/Feedback/FeedbackTriageEntityTest.php`:

```php
<?php

namespace Tests\Unit\Domain\Feedback;

use Domain\Feedback\Entity\FeedbackTriage;
use Domain\Feedback\ValueObject\TriageCategory;
use Domain\Feedback\ValueObject\TriageUrgency;
use PHPUnit\Framework\TestCase;

class FeedbackTriageEntityTest extends TestCase
{
    public function test_can_be_constructed(): void
    {
        $triagedAt = new \DateTimeImmutable('2026-04-11 12:00:00');

        $triage = new FeedbackTriage(
            id: 'triage-1',
            feedbackId: 'feedback-1',
            category: TriageCategory::Staff,
            urgency: TriageUrgency::High,
            suggestedResponse: 'We apologize for the experience.',
            rawLlmResponse: '{"category":"staff","urgency":"high","suggested_response":"We apologize for the experience."}',
            modelUsed: 'claude-haiku-4-5-20251001',
            triagedAt: $triagedAt,
        );

        $this->assertSame('triage-1', $triage->id);
        $this->assertSame('feedback-1', $triage->feedbackId);
        $this->assertSame(TriageCategory::Staff, $triage->category);
        $this->assertSame(TriageUrgency::High, $triage->urgency);
        $this->assertSame('We apologize for the experience.', $triage->suggestedResponse);
        $this->assertSame('claude-haiku-4-5-20251001', $triage->modelUsed);
        $this->assertSame($triagedAt, $triage->triagedAt);
    }
}
```

- [ ] **Step 6: Run test to verify it fails**

Run: `./vendor/bin/sail artisan test --filter=FeedbackTriageEntityTest`
Expected: FAIL - class not found

- [ ] **Step 7: Implement FeedbackTriage entity**

Create `src/Domain/Feedback/Entity/FeedbackTriage.php`:

```php
<?php

declare(strict_types=1);

namespace Domain\Feedback\Entity;

use Domain\Feedback\ValueObject\TriageCategory;
use Domain\Feedback\ValueObject\TriageUrgency;

final class FeedbackTriage
{
    public function __construct(
        public readonly string $id,
        public readonly string $feedbackId,
        public readonly TriageCategory $category,
        public readonly TriageUrgency $urgency,
        public readonly string $suggestedResponse,
        public readonly string $rawLlmResponse,
        public readonly string $modelUsed,
        public readonly \DateTimeImmutable $triagedAt,
    ) {}
}
```

- [ ] **Step 8: Run all unit tests to verify they pass**

Run: `./vendor/bin/sail artisan test --filter=TriageCategoryTest --filter=TriageUrgencyTest --filter=FeedbackTriageEntityTest`
Expected: PASS (6 tests)

- [ ] **Step 9: Commit**

```bash
git add src/Domain/Feedback/ValueObject/TriageCategory.php src/Domain/Feedback/ValueObject/TriageUrgency.php src/Domain/Feedback/Entity/FeedbackTriage.php tests/Unit/Domain/Feedback/
git commit -m "feat(feedback): add triage value objects and entity"
```

---

### Task 2: Domain Event + Ports

**Files:**
- Create: `src/Domain/Feedback/Event/FeedbackTriaged.php`
- Create: `src/Domain/Feedback/Port/FeedbackTriageServiceInterface.php`
- Create: `src/Domain/Feedback/Port/FeedbackTriageRepositoryInterface.php`

- [ ] **Step 1: Create FeedbackTriaged event**

Create `src/Domain/Feedback/Event/FeedbackTriaged.php`:

```php
<?php

declare(strict_types=1);

namespace Domain\Feedback\Event;

use Domain\Shared\Event\DomainEventInterface;

final readonly class FeedbackTriaged implements DomainEventInterface
{
    public function __construct(
        public string $feedbackId,
        public ?string $triageId,
        public string $businessProfileId,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable,
    ) {}

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
```

- [ ] **Step 2: Create FeedbackTriageServiceInterface port**

Create `src/Domain/Feedback/Port/FeedbackTriageServiceInterface.php`:

```php
<?php

declare(strict_types=1);

namespace Domain\Feedback\Port;

use Domain\Feedback\Entity\FeedbackTriage;

interface FeedbackTriageServiceInterface
{
    public function triage(string $comment, int $score): FeedbackTriage;
}
```

- [ ] **Step 3: Create FeedbackTriageRepositoryInterface port**

Create `src/Domain/Feedback/Port/FeedbackTriageRepositoryInterface.php`:

```php
<?php

declare(strict_types=1);

namespace Domain\Feedback\Port;

use Domain\Feedback\Entity\FeedbackTriage;

interface FeedbackTriageRepositoryInterface
{
    public function save(FeedbackTriage $triage): void;

    public function findByFeedbackId(string $feedbackId): ?FeedbackTriage;
}
```

- [ ] **Step 4: Commit**

```bash
git add src/Domain/Feedback/Event/FeedbackTriaged.php src/Domain/Feedback/Port/FeedbackTriageServiceInterface.php src/Domain/Feedback/Port/FeedbackTriageRepositoryInterface.php
git commit -m "feat(feedback): add FeedbackTriaged event and triage ports"
```

---

### Task 3: Migration + Eloquent Model + Repository

**Files:**
- Create: `database/migrations/2026_04_11_000001_create_feedback_triages_table.php`
- Create: `app/Infrastructure/Persistence/Eloquent/FeedbackTriageModel.php`
- Create: `app/Infrastructure/Persistence/Repository/EloquentFeedbackTriageRepository.php`
- Modify: `app/Infrastructure/Persistence/Eloquent/FeedbackModel.php`
- Modify: `app/Providers/DomainServiceProvider.php`
- Test: `tests/Feature/Feedback/FeedbackTriageRepositoryTest.php`

- [ ] **Step 1: Write repository test**

Create `tests/Feature/Feedback/FeedbackTriageRepositoryTest.php`:

```php
<?php

namespace Tests\Feature\Feedback;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\RatingModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use Domain\Feedback\Entity\FeedbackTriage;
use Domain\Feedback\Port\FeedbackTriageRepositoryInterface;
use Domain\Feedback\ValueObject\TriageCategory;
use Domain\Feedback\ValueObject\TriageUrgency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTriageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FeedbackTriageRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(FeedbackTriageRepositoryInterface::class);
    }

    public function test_can_save_and_find_by_feedback_id(): void
    {
        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-t']);
        $profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz',
        ]);
        $rating = RatingModel::create([
            'business_profile_id' => $profile->id,
            'score' => 2,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Terrible service.',
        ]);

        $triage = new FeedbackTriage(
            id: 'triage-001',
            feedbackId: $feedback->id,
            category: TriageCategory::Staff,
            urgency: TriageUrgency::High,
            suggestedResponse: 'We sincerely apologize for this experience.',
            rawLlmResponse: '{"category":"staff","urgency":"high","suggested_response":"We sincerely apologize for this experience."}',
            modelUsed: 'claude-haiku-4-5-20251001',
            triagedAt: new \DateTimeImmutable('2026-04-11 12:00:00'),
        );

        $this->repository->save($triage);

        $found = $this->repository->findByFeedbackId($feedback->id);

        $this->assertNotNull($found);
        $this->assertSame('triage-001', $found->id);
        $this->assertSame($feedback->id, $found->feedbackId);
        $this->assertSame(TriageCategory::Staff, $found->category);
        $this->assertSame(TriageUrgency::High, $found->urgency);
        $this->assertSame('We sincerely apologize for this experience.', $found->suggestedResponse);
        $this->assertSame('claude-haiku-4-5-20251001', $found->modelUsed);
    }

    public function test_returns_null_when_not_found(): void
    {
        $this->assertNull($this->repository->findByFeedbackId('nonexistent'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/sail artisan test --filter=FeedbackTriageRepositoryTest`
Expected: FAIL - table/class not found

- [ ] **Step 3: Create migration**

Create `database/migrations/2026_04_11_000001_create_feedback_triages_table.php`:

```php
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
```

- [ ] **Step 4: Create FeedbackTriageModel**

Create `app/Infrastructure/Persistence/Eloquent/FeedbackTriageModel.php`:

```php
<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackTriageModel extends Model
{
    use HasUuids;

    protected $table = 'feedback_triages';

    protected $fillable = [
        'id',
        'feedback_id',
        'category',
        'urgency',
        'suggested_response',
        'raw_llm_response',
        'model_used',
        'triaged_at',
    ];

    protected function casts(): array
    {
        return [
            'triaged_at' => 'datetime',
        ];
    }

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(FeedbackModel::class, 'feedback_id');
    }
}
```

- [ ] **Step 5: Add triage relationship to FeedbackModel**

In `app/Infrastructure/Persistence/Eloquent/FeedbackModel.php`, add after the `rating()` method:

```php
public function triage(): \Illuminate\Database\Eloquent\Relations\HasOne
{
    return $this->hasOne(FeedbackTriageModel::class, 'feedback_id');
}
```

- [ ] **Step 6: Create EloquentFeedbackTriageRepository**

Create `app/Infrastructure/Persistence/Repository/EloquentFeedbackTriageRepository.php`:

```php
<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\FeedbackTriageModel;
use Domain\Feedback\Entity\FeedbackTriage;
use Domain\Feedback\Port\FeedbackTriageRepositoryInterface;
use Domain\Feedback\ValueObject\TriageCategory;
use Domain\Feedback\ValueObject\TriageUrgency;

class EloquentFeedbackTriageRepository implements FeedbackTriageRepositoryInterface
{
    public function save(FeedbackTriage $triage): void
    {
        FeedbackTriageModel::updateOrCreate(
            ['id' => $triage->id],
            [
                'id' => $triage->id,
                'feedback_id' => $triage->feedbackId,
                'category' => $triage->category->value,
                'urgency' => $triage->urgency->value,
                'suggested_response' => $triage->suggestedResponse,
                'raw_llm_response' => $triage->rawLlmResponse,
                'model_used' => $triage->modelUsed,
                'triaged_at' => $triage->triagedAt,
            ]
        );
    }

    public function findByFeedbackId(string $feedbackId): ?FeedbackTriage
    {
        $model = FeedbackTriageModel::where('feedback_id', $feedbackId)->first();

        return $model ? $this->toDomain($model) : null;
    }

    private function toDomain(FeedbackTriageModel $model): FeedbackTriage
    {
        return new FeedbackTriage(
            id: $model->id,
            feedbackId: $model->feedback_id,
            category: TriageCategory::from($model->category),
            urgency: TriageUrgency::from($model->urgency),
            suggestedResponse: $model->suggested_response,
            rawLlmResponse: $model->raw_llm_response,
            modelUsed: $model->model_used,
            triagedAt: new \DateTimeImmutable($model->triaged_at->toDateTimeString()),
        );
    }
}
```

- [ ] **Step 7: Register bindings in DomainServiceProvider**

In `app/Providers/DomainServiceProvider.php`, add two entries to the `$bindings` array:

```php
use App\Infrastructure\Persistence\Repository\EloquentFeedbackTriageRepository;
use Domain\Feedback\Port\FeedbackTriageRepositoryInterface;
```

Add to `$bindings`:
```php
FeedbackTriageRepositoryInterface::class => EloquentFeedbackTriageRepository::class,
```

(The `FeedbackTriageServiceInterface` binding will be added in Task 5 when the adapter is built.)

- [ ] **Step 8: Run test to verify it passes**

Run: `./vendor/bin/sail artisan test --filter=FeedbackTriageRepositoryTest`
Expected: PASS (2 tests)

- [ ] **Step 9: Commit**

```bash
git add database/migrations/2026_04_11_000001_create_feedback_triages_table.php app/Infrastructure/Persistence/Eloquent/FeedbackTriageModel.php app/Infrastructure/Persistence/Eloquent/FeedbackModel.php app/Infrastructure/Persistence/Repository/EloquentFeedbackTriageRepository.php app/Providers/DomainServiceProvider.php tests/Feature/Feedback/FeedbackTriageRepositoryTest.php
git commit -m "feat(feedback): add feedback_triages table, model, and repository"
```

---

### Task 4: Config + Claude Triage Service Adapter

**Files:**
- Modify: `config/services.php`
- Create: `app/Infrastructure/Ai/ClaudeFeedbackTriageService.php`
- Modify: `app/Providers/DomainServiceProvider.php`

- [ ] **Step 1: Add anthropic config to services.php**

In `config/services.php`, add before the closing `];`:

```php
'anthropic' => [
    'api_key' => env('ANTHROPIC_API_KEY'),
    'triage_model' => env('ANTHROPIC_TRIAGE_MODEL', 'claude-haiku-4-5-20251001'),
],
```

- [ ] **Step 2: Create ClaudeFeedbackTriageService**

Create `app/Infrastructure/Ai/ClaudeFeedbackTriageService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Ai;

use Domain\Feedback\Entity\FeedbackTriage;
use Domain\Feedback\Port\FeedbackTriageServiceInterface;
use Domain\Feedback\ValueObject\TriageCategory;
use Domain\Feedback\ValueObject\TriageUrgency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClaudeFeedbackTriageService implements FeedbackTriageServiceInterface
{
    public function triage(string $comment, int $score): FeedbackTriage
    {
        $model = config('services.anthropic.triage_model');

        $prompt = <<<PROMPT
You are a customer feedback analyst for a local business. Analyze this negative customer feedback and provide a structured triage.

Customer rating: {$score}/5
Customer comment: {$comment}

Respond with ONLY a JSON object (no markdown, no explanation) with these exact keys:
- "category": one of "staff", "wait_time", "product_quality", "pricing", "environment", "communication", "other"
- "urgency": one of "low", "medium", "high"
  - low: general dissatisfaction, minor complaint
  - medium: specific issue, disappointed but not escalating
  - high: emotional intensity, mentions of legal action, social media threats, health/safety concerns
- "suggested_response": a 2-3 sentence professional response the business owner can send to the customer. Be empathetic and offer to make it right.
PROMPT;

        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => 512,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->failed()) {
            Log::error('Claude API triage failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Failed to triage feedback: ' . $response->status());
        }

        $text = $response->json('content.0.text', '{}');

        $text = preg_replace('/^```(?:json)?\s*\n?/i', '', $text);
        $text = preg_replace('/\n?```\s*$/i', '', $text);

        $data = json_decode(trim($text), true);

        if (! $data || ! isset($data['category'], $data['urgency'], $data['suggested_response'])) {
            Log::error('Claude API triage returned invalid JSON', ['raw' => $text]);
            throw new \RuntimeException('Invalid triage response from LLM');
        }

        $category = TriageCategory::tryFrom($data['category']) ?? TriageCategory::Other;
        $urgency = TriageUrgency::tryFrom($data['urgency']) ?? TriageUrgency::Medium;

        return new FeedbackTriage(
            id: (string) Str::uuid(),
            feedbackId: '',
            category: $category,
            urgency: $urgency,
            suggestedResponse: $data['suggested_response'],
            rawLlmResponse: $text,
            modelUsed: $model,
            triagedAt: new \DateTimeImmutable,
        );
    }
}
```

Note: The `feedbackId` is set to `''` here because the service doesn't know it - the listener will set the correct `feedbackId` when constructing the final entity to save. See Task 6 for how this is handled.

- [ ] **Step 3: Register binding in DomainServiceProvider**

In `app/Providers/DomainServiceProvider.php`, add imports:

```php
use App\Infrastructure\Ai\ClaudeFeedbackTriageService;
use Domain\Feedback\Port\FeedbackTriageServiceInterface;
```

Add to `$bindings`:
```php
FeedbackTriageServiceInterface::class => ClaudeFeedbackTriageService::class,
```

- [ ] **Step 4: Commit**

```bash
git add config/services.php app/Infrastructure/Ai/ClaudeFeedbackTriageService.php app/Providers/DomainServiceProvider.php
git commit -m "feat(feedback): add Claude triage service adapter and config"
```

---

### Task 5: TriageNegativeFeedback Listener

**Files:**
- Create: `app/Listeners/TriageNegativeFeedback.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `tests/Feature/Feedback/TriageNegativeFeedbackListenerTest.php`

- [ ] **Step 1: Write listener test**

Create `tests/Feature/Feedback/TriageNegativeFeedbackListenerTest.php`:

```php
<?php

namespace Tests\Feature\Feedback;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\RatingModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use Domain\Feedback\Entity\FeedbackTriage;
use Domain\Feedback\Event\FeedbackTriaged;
use Domain\Feedback\Event\NegativeFeedbackReceived;
use Domain\Feedback\Port\FeedbackTriageServiceInterface;
use Domain\Feedback\ValueObject\TriageCategory;
use Domain\Feedback\ValueObject\TriageUrgency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TriageNegativeFeedbackListenerTest extends TestCase
{
    use RefreshDatabase;

    private BusinessProfileModel $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-t']);
        $this->profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz',
        ]);
    }

    public function test_triages_feedback_and_dispatches_event(): void
    {
        Event::fake([FeedbackTriaged::class]);

        $mockTriage = new FeedbackTriage(
            id: 'triage-mock',
            feedbackId: '',
            category: TriageCategory::WaitTime,
            urgency: TriageUrgency::Medium,
            suggestedResponse: 'We are sorry about the wait.',
            rawLlmResponse: '{"category":"wait_time","urgency":"medium","suggested_response":"We are sorry about the wait."}',
            modelUsed: 'claude-haiku-4-5-20251001',
            triagedAt: new \DateTimeImmutable,
        );

        $this->mock(FeedbackTriageServiceInterface::class)
            ->shouldReceive('triage')
            ->once()
            ->andReturn($mockTriage);

        $rating = RatingModel::create([
            'business_profile_id' => $this->profile->id,
            'score' => 2,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Had to wait 45 minutes.',
        ]);

        $event = new NegativeFeedbackReceived(
            feedbackId: $feedback->id,
            ratingId: $rating->id,
            businessProfileId: $this->profile->id,
        );

        $listener = $this->app->make(\App\Listeners\TriageNegativeFeedback::class);
        $listener->handle($event);

        $this->assertDatabaseHas('feedback_triages', [
            'feedback_id' => $feedback->id,
            'category' => 'wait_time',
            'urgency' => 'medium',
        ]);

        Event::assertDispatched(FeedbackTriaged::class, function ($e) use ($feedback) {
            return $e->feedbackId === $feedback->id && $e->triageId !== null;
        });
    }

    public function test_dispatches_event_with_null_triage_on_failure(): void
    {
        Event::fake([FeedbackTriaged::class]);

        $this->mock(FeedbackTriageServiceInterface::class)
            ->shouldReceive('triage')
            ->once()
            ->andThrow(new \RuntimeException('API error'));

        $rating = RatingModel::create([
            'business_profile_id' => $this->profile->id,
            'score' => 1,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Horrible.',
        ]);

        $event = new NegativeFeedbackReceived(
            feedbackId: $feedback->id,
            ratingId: $rating->id,
            businessProfileId: $this->profile->id,
        );

        $listener = $this->app->make(\App\Listeners\TriageNegativeFeedback::class);
        $listener->handle($event);

        $this->assertDatabaseMissing('feedback_triages', [
            'feedback_id' => $feedback->id,
        ]);

        Event::assertDispatched(FeedbackTriaged::class, function ($e) use ($feedback) {
            return $e->feedbackId === $feedback->id && $e->triageId === null;
        });
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/sail artisan test --filter=TriageNegativeFeedbackListenerTest`
Expected: FAIL - listener class not found

- [ ] **Step 3: Implement TriageNegativeFeedback listener**

Create `app/Listeners/TriageNegativeFeedback.php`:

```php
<?php

namespace App\Listeners;

use Domain\Feedback\Entity\FeedbackTriage;
use Domain\Feedback\Event\FeedbackTriaged;
use Domain\Feedback\Event\NegativeFeedbackReceived;
use Domain\Feedback\Port\FeedbackRepositoryInterface;
use Domain\Feedback\Port\FeedbackTriageRepositoryInterface;
use Domain\Feedback\Port\FeedbackTriageServiceInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TriageNegativeFeedback implements ShouldQueue
{
    public function __construct(
        private FeedbackTriageServiceInterface $triageService,
        private FeedbackTriageRepositoryInterface $triageRepository,
        private FeedbackRepositoryInterface $feedbackRepository,
        private Dispatcher $eventDispatcher,
    ) {}

    public function handle(NegativeFeedbackReceived $event): void
    {
        $triageId = null;

        try {
            $feedback = $this->feedbackRepository->findById($event->feedbackId);

            if (! $feedback) {
                Log::warning('Feedback not found for triage', ['feedbackId' => $event->feedbackId]);
                return;
            }

            $result = $this->triageService->triage($feedback->comment, $feedback->score ?? 1);

            $triage = new FeedbackTriage(
                id: (string) Str::uuid(),
                feedbackId: $event->feedbackId,
                category: $result->category,
                urgency: $result->urgency,
                suggestedResponse: $result->suggestedResponse,
                rawLlmResponse: $result->rawLlmResponse,
                modelUsed: $result->modelUsed,
                triagedAt: $result->triagedAt,
            );

            $this->triageRepository->save($triage);
            $triageId = $triage->id;
        } catch (\Throwable $e) {
            Log::error('Feedback triage failed', [
                'feedbackId' => $event->feedbackId,
                'error' => $e->getMessage(),
            ]);
        }

        $this->eventDispatcher->dispatch(new FeedbackTriaged(
            feedbackId: $event->feedbackId,
            triageId: $triageId,
            businessProfileId: $event->businessProfileId,
        ));
    }
}
```

- [ ] **Step 4: Wire up the listener in AppServiceProvider**

In `app/Providers/AppServiceProvider.php`, update the `boot()` method:

Replace:
```php
Event::listen(NegativeFeedbackReceived::class, NotifyOwnerOnNegativeFeedback::class);
```

With:
```php
Event::listen(NegativeFeedbackReceived::class, \App\Listeners\TriageNegativeFeedback::class);
Event::listen(FeedbackTriaged::class, NotifyOwnerOnNegativeFeedback::class);
```

Add imports:
```php
use Domain\Feedback\Event\FeedbackTriaged;
```

- [ ] **Step 5: Run test to verify it passes**

Run: `./vendor/bin/sail artisan test --filter=TriageNegativeFeedbackListenerTest`
Expected: PASS (2 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Listeners/TriageNegativeFeedback.php app/Providers/AppServiceProvider.php tests/Feature/Feedback/TriageNegativeFeedbackListenerTest.php
git commit -m "feat(feedback): add TriageNegativeFeedback listener with event chain"
```

---

### Task 6: Update NotifyOwnerOnNegativeFeedback + Email Template

**Files:**
- Modify: `app/Listeners/NotifyOwnerOnNegativeFeedback.php`
- Create: `resources/views/mail/negative-feedback.blade.php`
- Test: `tests/Feature/Feedback/NotifyOwnerOnNegativeFeedbackTest.php`

- [ ] **Step 1: Write notification test**

Create `tests/Feature/Feedback/NotifyOwnerOnNegativeFeedbackTest.php`:

```php
<?php

namespace Tests\Feature\Feedback;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackTriageModel;
use App\Infrastructure\Persistence\Eloquent\RatingModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use App\Models\User;
use Domain\Feedback\Event\FeedbackTriaged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotifyOwnerOnNegativeFeedbackTest extends TestCase
{
    use RefreshDatabase;

    private TenantModel $tenant;
    private BusinessProfileModel $profile;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-t']);
        $this->owner = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->profile = BusinessProfileModel::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz',
        ]);
    }

    public function test_sends_enriched_email_when_triage_exists(): void
    {
        Mail::fake();

        $rating = RatingModel::create([
            'business_profile_id' => $this->profile->id,
            'score' => 2,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Staff was rude.',
        ]);
        $triage = FeedbackTriageModel::create([
            'feedback_id' => $feedback->id,
            'category' => 'staff',
            'urgency' => 'high',
            'suggested_response' => 'We sincerely apologize for this experience.',
            'raw_llm_response' => '{}',
            'model_used' => 'claude-haiku-4-5-20251001',
            'triaged_at' => now(),
        ]);

        $event = new FeedbackTriaged(
            feedbackId: $feedback->id,
            triageId: $triage->id,
            businessProfileId: $this->profile->id,
        );

        $listener = $this->app->make(\App\Listeners\NotifyOwnerOnNegativeFeedback::class);
        $listener->handle($event);

        Mail::assertSent(function (\Illuminate\Mail\Mailable $mail) {
            return $mail->hasTo($this->owner->email);
        });
    }

    public function test_sends_fallback_email_when_triage_is_null(): void
    {
        Mail::fake();

        $rating = RatingModel::create([
            'business_profile_id' => $this->profile->id,
            'score' => 1,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Awful.',
        ]);

        $event = new FeedbackTriaged(
            feedbackId: $feedback->id,
            triageId: null,
            businessProfileId: $this->profile->id,
        );

        $listener = $this->app->make(\App\Listeners\NotifyOwnerOnNegativeFeedback::class);
        $listener->handle($event);

        Mail::assertSent(function (\Illuminate\Mail\Mailable $mail) {
            return $mail->hasTo($this->owner->email);
        });
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/sail artisan test --filter=NotifyOwnerOnNegativeFeedbackTest`
Expected: FAIL - listener expects NegativeFeedbackReceived but we pass FeedbackTriaged

- [ ] **Step 3: Create email Blade template**

Create `resources/views/mail/negative-feedback.blade.php`:

```blade
# New Feedback for {{ $businessName }}

@if($triage)
**Category:** {{ ucfirst(str_replace('_', ' ', $triage->category)) }}
**Urgency:** {{ ucfirst($triage->urgency) }}

---
@endif

**Customer said:**
> {{ $comment }}

@if($triage)
---

**Suggested response:**
{{ $triage->suggested_response }}
@endif
```

- [ ] **Step 4: Update NotifyOwnerOnNegativeFeedback listener**

Replace the full contents of `app/Listeners/NotifyOwnerOnNegativeFeedback.php`:

```php
<?php

namespace App\Listeners;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackTriageModel;
use App\Models\User;
use Domain\Feedback\Event\FeedbackTriaged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotifyOwnerOnNegativeFeedback implements ShouldQueue
{
    public function handle(FeedbackTriaged $event): void
    {
        $business = BusinessProfileModel::find($event->businessProfileId);

        if (! $business) {
            return;
        }

        $owner = User::where('tenant_id', $business->tenant_id)->first();

        if (! $owner) {
            return;
        }

        $feedback = FeedbackModel::find($event->feedbackId);
        $triage = $event->triageId ? FeedbackTriageModel::find($event->triageId) : null;

        Mail::send('mail.negative-feedback', [
            'businessName' => $business->name,
            'comment' => $feedback?->comment ?? '',
            'triage' => $triage,
        ], function ($message) use ($owner, $business) {
            $message->to($owner->email)
                ->subject(__('feedback.notification_subject', ['business' => $business->name]));
        });
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `./vendor/bin/sail artisan test --filter=NotifyOwnerOnNegativeFeedbackTest`
Expected: PASS (2 tests)

- [ ] **Step 6: Run existing RatingFlowTest to verify no regression**

Run: `./vendor/bin/sail artisan test --filter=RatingFlowTest`
Expected: PASS (4 tests)

- [ ] **Step 7: Commit**

```bash
git add app/Listeners/NotifyOwnerOnNegativeFeedback.php resources/views/mail/negative-feedback.blade.php tests/Feature/Feedback/NotifyOwnerOnNegativeFeedbackTest.php
git commit -m "feat(feedback): enriched notification email with triage data"
```

---

### Task 7: Update Dashboard Feedback List

**Files:**
- Modify: `app/Application/Query/GetFeedbackList.php`
- Modify: `app/Infrastructure/Persistence/Repository/EloquentFeedbackRepository.php`
- Modify: `resources/views/feedback/index.blade.php`
- Test: `tests/Feature/Feedback/GetFeedbackListWithTriageTest.php`
- Test: `tests/Feature/Feedback/FeedbackIndexViewTest.php`

- [ ] **Step 1: Write GetFeedbackList triage test**

Create `tests/Feature/Feedback/GetFeedbackListWithTriageTest.php`:

```php
<?php

namespace Tests\Feature\Feedback;

use App\Application\Query\GetFeedbackList;
use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackTriageModel;
use App\Infrastructure\Persistence\Eloquent\RatingModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetFeedbackListWithTriageTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_feedback_with_triage_data(): void
    {
        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-t']);
        $profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz',
        ]);
        $rating = RatingModel::create([
            'business_profile_id' => $profile->id,
            'score' => 2,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Bad service.',
        ]);
        FeedbackTriageModel::create([
            'feedback_id' => $feedback->id,
            'category' => 'staff',
            'urgency' => 'high',
            'suggested_response' => 'We apologize.',
            'raw_llm_response' => '{}',
            'model_used' => 'test-model',
            'triaged_at' => now(),
        ]);

        $query = $this->app->make(GetFeedbackList::class);
        $results = $query->execute($profile->id);

        $this->assertCount(1, $results);
        $this->assertNotNull($results[0]->triage);
        $this->assertSame('staff', $results[0]->triage->category->value);
        $this->assertSame('high', $results[0]->triage->urgency->value);
        $this->assertSame('We apologize.', $results[0]->triage->suggestedResponse);
    }

    public function test_returns_feedback_without_triage(): void
    {
        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-t']);
        $profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz',
        ]);
        $rating = RatingModel::create([
            'business_profile_id' => $profile->id,
            'score' => 3,
            'source' => 'email',
        ]);
        FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Meh.',
        ]);

        $query = $this->app->make(GetFeedbackList::class);
        $results = $query->execute($profile->id);

        $this->assertCount(1, $results);
        $this->assertNull($results[0]->triage);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/sail artisan test --filter=GetFeedbackListWithTriageTest`
Expected: FAIL - `triage` property does not exist on Feedback entity

- [ ] **Step 3: Add triage property to Feedback entity**

In `src/Domain/Feedback/Entity/Feedback.php`, add the triage property:

```php
<?php

declare(strict_types=1);

namespace Domain\Feedback\Entity;

use Domain\Feedback\Entity\FeedbackTriage;

final class Feedback
{
    public function __construct(
        public readonly string $id,
        public readonly string $ratingId,
        public readonly string $comment,
        public readonly ?\Domain\Shared\ValueObject\Email $contactEmail = null,
        public readonly ?int $score = null,
        public readonly ?FeedbackTriage $triage = null,
    ) {}
}
```

- [ ] **Step 4: Update EloquentFeedbackRepository to eager-load triage**

In `app/Infrastructure/Persistence/Repository/EloquentFeedbackRepository.php`, update the `findByBusinessProfileId` method to eager-load triage:

Change:
```php
->with('rating')
```

To:
```php
->with(['rating', 'triage'])
```

Update the `toDomain` method to include triage mapping:

```php
private function toDomain(FeedbackModel $model): Feedback
{
    $triage = null;

    if ($model->relationLoaded('triage') && $model->triage) {
        $triage = new \Domain\Feedback\Entity\FeedbackTriage(
            id: $model->triage->id,
            feedbackId: $model->triage->feedback_id,
            category: \Domain\Feedback\ValueObject\TriageCategory::from($model->triage->category),
            urgency: \Domain\Feedback\ValueObject\TriageUrgency::from($model->triage->urgency),
            suggestedResponse: $model->triage->suggested_response,
            rawLlmResponse: $model->triage->raw_llm_response,
            modelUsed: $model->triage->model_used,
            triagedAt: new \DateTimeImmutable($model->triage->triaged_at->toDateTimeString()),
        );
    }

    return new Feedback(
        id: $model->id,
        ratingId: $model->rating_id,
        comment: $model->comment,
        contactEmail: $model->contact_email ? new \Domain\Shared\ValueObject\Email($model->contact_email) : null,
        score: $model->relationLoaded('rating') ? (int) $model->rating?->score : null,
        triage: $triage,
    );
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `./vendor/bin/sail artisan test --filter=GetFeedbackListWithTriageTest`
Expected: PASS (2 tests)

- [ ] **Step 6: Write dashboard view test**

Create `tests/Feature/Feedback/FeedbackIndexViewTest.php`:

```php
<?php

namespace Tests\Feature\Feedback;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackTriageModel;
use App\Infrastructure\Persistence\Eloquent\RatingModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackIndexViewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private BusinessProfileModel $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-t']);
        $this->user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz',
        ]);
    }

    public function test_shows_triage_card_when_triage_exists(): void
    {
        $rating = RatingModel::create([
            'business_profile_id' => $this->profile->id,
            'score' => 2,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Staff was rude.',
        ]);
        FeedbackTriageModel::create([
            'feedback_id' => $feedback->id,
            'category' => 'staff',
            'urgency' => 'high',
            'suggested_response' => 'We sincerely apologize.',
            'raw_llm_response' => '{}',
            'model_used' => 'test-model',
            'triaged_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get("/business-profiles/{$this->profile->id}/feedback");

        $response->assertStatus(200);
        $response->assertSee('Staff was rude.');
        $response->assertSee('Staff');
        $response->assertSee('High');
        $response->assertSee('We sincerely apologize.');
    }

    public function test_shows_plain_card_when_no_triage(): void
    {
        $rating = RatingModel::create([
            'business_profile_id' => $this->profile->id,
            'score' => 3,
            'source' => 'email',
        ]);
        FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Could be better.',
        ]);

        $response = $this->actingAs($this->user)
            ->get("/business-profiles/{$this->profile->id}/feedback");

        $response->assertStatus(200);
        $response->assertSee('Could be better.');
        $response->assertDontSee('Suggested response');
    }
}
```

- [ ] **Step 7: Run test to verify it fails**

Run: `./vendor/bin/sail artisan test --filter=FeedbackIndexViewTest`
Expected: FAIL - view does not contain triage elements

- [ ] **Step 8: Update feedback index view**

Replace the full contents of `resources/views/feedback/index.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <x-breadcrumbs :items="[
            ['label' => __('business.title'), 'url' => route('business-profiles.index')],
            ['label' => $profile->name, 'url' => route('business-profiles.show', $profile->id)],
            ['label' => __('feedback.title')],
        ]" />
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('feedback.title') }} — {{ $profile->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @forelse($feedbackList as $feedback)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 p-6">
                    <div class="flex items-start justify-between">
                        <p class="text-gray-900">{{ $feedback->comment }}</p>
                        @if($feedback->score)
                            <span class="shrink-0 ml-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ $feedback->score >= 4 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $feedback->score }}/5
                            </span>
                        @endif
                    </div>
                    @if($feedback->contactEmail)
                        <p class="text-sm text-gray-500 mt-2">{{ __('feedback.contact') }}: {{ $feedback->contactEmail->value }}</p>
                    @endif

                    @if($feedback->triage)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ ucfirst(str_replace('_', ' ', $feedback->triage->category->value)) }}
                                </span>
                                @php
                                    $urgencyColors = [
                                        'low' => 'bg-green-100 text-green-800',
                                        'medium' => 'bg-yellow-100 text-yellow-800',
                                        'high' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $urgencyColors[$feedback->triage->urgency->value] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($feedback->triage->urgency->value) }}
                                </span>
                            </div>
                            <div x-data="{ copied: false }" class="bg-gray-50 rounded-md p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-medium text-gray-500">Suggested response</span>
                                    <button
                                        @click="navigator.clipboard.writeText($refs.response.textContent); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="text-xs text-indigo-600 hover:text-indigo-800"
                                    >
                                        <span x-show="!copied">Copy</span>
                                        <span x-show="copied" x-cloak>Copied!</span>
                                    </button>
                                </div>
                                <p x-ref="response" class="text-sm text-gray-700">{{ $feedback->triage->suggestedResponse }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    {{ __('feedback.no_feedback') }}
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
```

- [ ] **Step 9: Run test to verify it passes**

Run: `./vendor/bin/sail artisan test --filter=FeedbackIndexViewTest`
Expected: PASS (2 tests)

- [ ] **Step 10: Commit**

```bash
git add src/Domain/Feedback/Entity/Feedback.php app/Infrastructure/Persistence/Repository/EloquentFeedbackRepository.php app/Application/Query/GetFeedbackList.php resources/views/feedback/index.blade.php tests/Feature/Feedback/GetFeedbackListWithTriageTest.php tests/Feature/Feedback/FeedbackIndexViewTest.php
git commit -m "feat(feedback): display triage cards on dashboard feedback list"
```

---

### Task 8: Full Test Suite + Final Verification

**Files:** None new - this is a verification task.

- [ ] **Step 1: Run the full test suite**

Run: `./vendor/bin/sail composer test`
Expected: All tests pass, no regressions

- [ ] **Step 2: Run code formatting**

Run: `./vendor/bin/sail exec laravel.test ./vendor/bin/pint`
Expected: Files formatted (if any changes needed)

- [ ] **Step 3: Commit formatting changes if any**

```bash
git add -A
git commit -m "style(feedback): apply pint formatting"
```

(Skip if Pint reports no changes.)

- [ ] **Step 4: Run migrations to verify they work**

Run: `./vendor/bin/sail artisan migrate:fresh`
Expected: All migrations run without error, `feedback_triages` table created

- [ ] **Step 5: Final commit - done**

Verify `git log --oneline` shows the task commits. Feature is complete.
