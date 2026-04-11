# Negative Feedback Triage + Action Summary

**Date:** 2026-04-11
**Status:** Design approved, pending implementation plan
**Growth lever:** Activation + retention
**Build complexity:** S

## Overview

When a customer submits 1-3 star feedback, an AI model (Claude Haiku) analyzes the comment and extracts a structured triage: root cause category, urgency level, and a suggested response script. The triage result is stored separately from the feedback, surfaced in an enriched owner notification email, and displayed as a structured card on the dashboard feedback list.

## Decisions Log

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Output surface | Email + dashboard (both) | SMB owners live in inbox, but activation lives in the dashboard |
| AI provider | Provider-agnostic port, Claude Haiku first | Follows existing DDD port/adapter pattern, avoids vendor lock-in |
| Timing | Async via event listener (queued) | Keeps customer-facing submit fast |
| Categories | Fixed set of 7 | Dashboard filterable, prompt simple, extensible later |
| Storage | Separate `feedback_triages` table | AI output decoupled from user data, re-runnable, debuggable |
| Architecture | Event listener chain (Approach A) | Natural extension of existing event flow, single-responsibility listeners |
| LLM failure handling | Dispatch FeedbackTriaged with null triageId | Notification listener falls back to raw email, no timing hacks |

## Domain Layer

### New Value Objects (`Domain\Feedback\ValueObject\`)

**TriageCategory** - enum
- `Staff` - rude, unhelpful, unprofessional
- `WaitTime` - too long, scheduling issues
- `ProductQuality` - defective, not as described
- `Pricing` - too expensive, hidden fees
- `Environment` - dirty, uncomfortable
- `Communication` - no follow-up, unclear info
- `Other` - fallback

**TriageUrgency** - enum
- `Low` - general dissatisfaction, minor complaint
- `Medium` - specific issue, disappointed but not escalating
- `High` - emotional intensity, mentions of legal action, social media threats, health/safety

### New Entity (`Domain\Feedback\Entity\FeedbackTriage`)

```
id: string (uuid)
feedbackId: string
category: TriageCategory
urgency: TriageUrgency
suggestedResponse: string
rawLlmResponse: string
modelUsed: string
triagedAt: DateTimeImmutable
```

### New Event (`Domain\Feedback\Event\FeedbackTriaged`)

```
feedbackId: string
triageId: ?string (null on LLM failure)
businessProfileId: string
occurredAt: DateTimeImmutable
```

### New Ports (`Domain\Feedback\Port\`)

**FeedbackTriageServiceInterface**
- `triage(string $comment, int $score): FeedbackTriage`
- The LLM abstraction. Domain defines what it needs, infrastructure provides how.

**FeedbackTriageRepositoryInterface**
- `save(FeedbackTriage $triage): void`
- `findByFeedbackId(string $feedbackId): ?FeedbackTriage`

## Infrastructure Layer

### New Eloquent Model (`App\Infrastructure\Persistence\Eloquent\FeedbackTriageModel`)

Table: `feedback_triages`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid | primary key |
| feedback_id | uuid | foreign key to `feedback`, unique, cascade delete |
| category | string | enum value as string |
| urgency | string | enum value as string |
| suggested_response | text | |
| raw_llm_response | text | full LLM JSON for debugging |
| model_used | string | e.g. `claude-haiku-4-5-20251001` |
| triaged_at | timestamp | |
| created_at | timestamp | |
| updated_at | timestamp | |

BelongsTo relationship to `FeedbackModel`.

### New Repository (`App\Infrastructure\Persistence\Repository\EloquentFeedbackTriageRepository`)

Implements `FeedbackTriageRepositoryInterface`. Standard save/find, maps between Eloquent model and domain entity.

### New LLM Adapter (`App\Infrastructure\Ai\ClaudeFeedbackTriageService`)

Implements `FeedbackTriageServiceInterface`.

- Lives in `App\Infrastructure\Ai` (new namespace - general AI adapter location, not Reddit-specific)
- Uses Anthropic Messages API with Haiku, same HTTP pattern as existing `ClaudeAiDrafter`
- Config: `services.anthropic.api_key`, `services.anthropic.triage_model`
- Prompt asks for JSON output with `category`, `urgency`, `suggested_response`
- Parses JSON, builds `FeedbackTriage` entity
- On API failure: throws exception (caller handles)

### Bindings (`DomainServiceProvider`)

```php
FeedbackTriageServiceInterface::class => ClaudeFeedbackTriageService::class,
FeedbackTriageRepositoryInterface::class => EloquentFeedbackTriageRepository::class,
```

## Event Flow

### Current

```
SubmitFeedback
  -> dispatches NegativeFeedbackReceived
     -> NotifyOwnerOnNegativeFeedback (sends raw email)
```

### New

```
SubmitFeedback
  -> dispatches NegativeFeedbackReceived
     -> TriageNegativeFeedback (NEW, queued)
          -> calls FeedbackTriageServiceInterface (LLM)
          -> saves FeedbackTriage via repository
          -> dispatches FeedbackTriaged
             -> NotifyOwnerOnNegativeFeedback (MOVED to FeedbackTriaged)
                  -> if triageId present: sends enriched email
                  -> if triageId null: sends raw fallback email
```

### New Listener (`App\Listeners\TriageNegativeFeedback`)

- Listens on `NegativeFeedbackReceived`, implements `ShouldQueue`
- Injected: `FeedbackTriageServiceInterface`, `FeedbackTriageRepositoryInterface`, `FeedbackRepositoryInterface`, `Dispatcher`
- Loads feedback by ID, calls triage service, saves result, dispatches `FeedbackTriaged`
- On LLM failure: logs error, dispatches `FeedbackTriaged` with null `triageId`

### Modified Listener (`App\Listeners\NotifyOwnerOnNegativeFeedback`)

- Moved from `NegativeFeedbackReceived` to `FeedbackTriaged`
- If `triageId` present: load triage, send enriched Blade email
- If `triageId` null: send existing raw-comment fallback email
- Switch from `Mail::raw()` to Blade template (`resources/views/mail/negative-feedback.blade.php`)

## Email Template

`resources/views/mail/negative-feedback.blade.php`

When triage is present:
- Category badge
- Urgency level (color-coded)
- Original customer comment
- Suggested response (copyable)

When triage is absent:
- Business name
- Original customer comment (same as current behavior)

## Dashboard Changes

### Feedback List (`resources/views/feedback/index.blade.php`)

Each feedback card gets an optional triage section below the comment:
- Category pill (styled by category)
- Urgency badge (green/yellow/red)
- Suggested response in a copyable block

If no triage data exists for a feedback, the card renders as-is (current behavior).

### Query Changes

`GetFeedbackList` updated to eager-load triage data via the repository. The repository returns triage data alongside feedback when available.

No new routes or controllers needed.

## Config

### `config/services.php`

```php
'anthropic' => [
    'api_key' => env('ANTHROPIC_API_KEY'),
    'triage_model' => env('ANTHROPIC_TRIAGE_MODEL', 'claude-haiku-4-5-20251001'),
],
```

### `.env`

```
ANTHROPIC_API_KEY=       # already exists for Reddit module
ANTHROPIC_TRIAGE_MODEL=  # optional override
```

Note: The Reddit module currently reads from `reddit.anthropic_api_key`. The triage adapter reads from `services.anthropic.api_key`. Consolidating the Reddit config is out of scope for this feature.

## Testing

### Unit Tests (`tests/Unit/`)

- `TriageCategoryTest` - enum values, all cases covered
- `TriageUrgencyTest` - enum values, all cases covered
- `FeedbackTriageEntityTest` - construction, properties

### Feature Tests (`tests/Feature/`)

- `TriageNegativeFeedbackListenerTest`
  - Mock `FeedbackTriageServiceInterface`, submit feedback, assert triage saved, assert `FeedbackTriaged` dispatched
  - Mock LLM failure, assert `FeedbackTriaged` dispatched with null `triageId`, assert error logged

- `NotifyOwnerOnNegativeFeedbackTest`
  - With triage: assert enriched email with category, urgency, suggested response
  - Without triage (null `triageId`): assert fallback raw email

- `FeedbackTriageRepositoryTest`
  - Save and retrieve by feedback ID

- `GetFeedbackListTest`
  - Triage included when present
  - Feedback returned without triage when absent

- `FeedbackIndexViewTest`
  - With triage: category pill, urgency badge, suggested response visible
  - Without triage: card renders without triage section

No LLM integration tests - the adapter is tested via mock at the port level.
