# Reddit Marketing Agent — Design Spec

**Date:** 2026-03-29
**Status:** Approved
**Goal:** Semi-autonomous Reddit marketing system that finds relevant threads, drafts human-like responses via Claude API, and publishes after manual approval.

---

## 1. System Overview

Four scheduled jobs orchestrate the pipeline (Lurker removed — automated upvoting violates Reddit ToS):

| Job | Schedule | Phase | Purpose |
|-----|----------|-------|---------|
| Scout | Every 2 hours | All | Search Reddit API for relevant threads across target subreddits |
| Drafter | Daily at 07:00 | Days 15+ | Generate tailored draft responses via Claude API |
| Publisher | Every 15 min | Days 15+ | Post approved drafts to Reddit |
| Strategist | Weekly (Sundays) | All | Audit performance, check ratios, suggest strategy adjustments |

All jobs use `withoutOverlapping()` to prevent concurrent execution.

### Admin Dashboard (`/admin/reddit`)

- Review pending drafts: approve, edit + approve, reject, or retry failed
- Published posts with Reddit metrics (upvotes, replies)
- Weekly strategy reports
- Phase status indicator and content ratio tracker
- Subreddit configuration management

---

## 2. 90-Day Phase System

The system tracks Reddit account creation date and enforces behavior automatically.

| Phase | Days | Scout | Drafter | Publisher |
|-------|------|-------|---------|-----------|
| Lurk | 1-14 | Active (observe only, bookmark threads) | Disabled | Disabled |
| Comment | 15-30 | Active | Comments only | Comments only |
| Full | 31+ | Active | Comments + posts | All approved content |

Phase is determined by `REDDIT_ACCOUNT_CREATED_AT` env value, not calendar date.

During the Lurk phase, Scout saves threads for observation but no automated actions are taken. Manual engagement (upvoting, commenting) should be done by the account owner directly in Reddit.

---

## 3. Content Mix Enforcement (70/20/10)

The Drafter tracks content type counts over a rolling 30-day window:

| Type | Target % | Description |
|------|----------|-------------|
| `value` | 70% | Pure helpful content, no product mention |
| `discussion` | 20% | Questions, observations, trend discussions |
| `brand` | 10% | Product mention (only in designated threads or when asked) |

Before generating a draft, Drafter checks the current ratio and assigns the appropriate content type. Brand content is only generated when the ratio allows AND the thread is a "what tools?" type or a designated self-promo thread.

---

## 4. Data Model

### `reddit_subreddits`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| name | string | Subreddit name (e.g., "smallbusiness") |
| tier | tinyint | 1-4, from playbook |
| max_posts_per_week | tinyint | Cadence limit |
| max_comments_per_week | tinyint | Cadence limit |
| rules_json | json nullable | Per-sub rules (no_links, self_promo_thread_only, etc.) |
| keywords_json | json nullable | Override keywords for this sub |
| is_active | boolean | Enable/disable |
| created_at / updated_at | timestamps | |

Seeded with 20+ subreddits from the playbook (tiers, cadences, rules).

### `reddit_threads`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| reddit_subreddit_id | bigint FK | |
| reddit_id | string unique | Reddit's thing ID (e.g., "t3_abc123") |
| title | string | Thread title |
| body | text nullable | Thread body/selftext |
| author | string | Reddit username |
| url | string | Full Reddit URL |
| score | integer | Upvotes at time of discovery |
| num_comments | integer | Comment count at discovery |
| thread_type | enum | `how_to_get_reviews`, `negative_review_help`, `starting_business`, `tool_recommendation`, `local_seo`, `general` |
| status | enum | `new`, `drafting`, `drafted`, `skipped`, `stale` |
| discovered_at | timestamp | When Scout found it |
| created_at / updated_at | timestamps | |

Threads older than 24 hours at draft time are marked `stale` and skipped (Reddit engagement drops off sharply after day 1).

### `reddit_drafts`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| reddit_thread_id | bigint FK nullable | Null for standalone posts |
| reddit_subreddit_id | bigint FK | |
| type | enum | `comment`, `post` |
| content_category | enum | `value`, `discussion`, `brand` |
| title | string nullable | For posts only |
| body | text | Draft content |
| status | enum | `pending`, `approved`, `rejected`, `published`, `failed` |
| reddit_thing_id | string nullable | Reddit's ID after publishing |
| published_at | timestamp nullable | |
| approved_at | timestamp nullable | |
| rejected_at | timestamp nullable | |
| rejection_reason | string nullable | |
| reddit_score | integer nullable | Upvotes after publishing |
| reddit_num_comments | integer nullable | Replies after publishing |
| created_at / updated_at | timestamps | |

Failed drafts can be retried from the dashboard (sets status back to `approved`).

### `reddit_strategy_reports`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| period_start | date | |
| period_end | date | |
| report_json | json | Full strategy analysis |
| recommendations_json | json | Actionable suggestions |
| content_ratio_json | json | Actual 70/20/10 breakdown |
| top_performing_json | json | Best drafts by upvotes |
| created_at / updated_at | timestamps | |

### Environment Variables

```
REDDIT_CLIENT_ID=
REDDIT_CLIENT_SECRET=
REDDIT_USERNAME=
REDDIT_PASSWORD=
REDDIT_USER_AGENT="web:quickfeedback:v1.0 (by /u/YOUR_USERNAME)"
REDDIT_ACCOUNT_CREATED_AT=2026-03-29
REDDIT_DRY_RUN=false
REDDIT_ENABLED=true

ANTHROPIC_API_KEY=
REDDIT_DRAFTER_MODEL=claude-haiku-4-5-20251001
REDDIT_STRATEGIST_MODEL=claude-sonnet-4-6
```

---

## 5. Component Architecture

Follows existing DDD patterns exactly (verified against codebase).

### Domain Layer (`src/Domain/Reddit/`)

**Entities** (`src/Domain/Reddit/Entity/`):
- `RedditThread` — Thread with type classification and status
- `RedditDraft` — Draft with content category and status lifecycle
- `RedditSubreddit` — Subreddit with tier, cadence limits, and rules
- `RedditStrategyReport` — Weekly strategy report

**Value Objects** (`src/Domain/Reddit/`):
- `ContentMixPolicy` — Enforces 70/20/10 ratio over rolling 30-day window
- `PhasePolicy` — Determines current phase from account age
- `SubredditCadencePolicy` — Enforces per-tier posting limits
- `SubredditRules` — Typed accessor for `rules_json` (validates structure)
- `SubredditKeywords` — Typed accessor for `keywords_json` (validates structure)
- `ThreadType` — Enum for thread classification

**Port Interfaces** (`src/Domain/Reddit/Port/`):
- `RedditApiInterface` — Reddit API operations (search, submit, OAuth)
- `AiDrafterInterface` — AI draft generation
- `RedditThreadRepositoryInterface` — Thread persistence
- `RedditDraftRepositoryInterface` — Draft persistence
- `RedditSubredditRepositoryInterface` — Subreddit persistence
- `RedditStrategyReportRepositoryInterface` — Report persistence

### Infrastructure Layer

**Eloquent Models** (`app/Infrastructure/Persistence/Eloquent/`):
- `RedditThreadModel`
- `RedditDraftModel`
- `RedditSubredditModel`
- `RedditStrategyReportModel`

**Repositories** (`app/Infrastructure/Persistence/Repository/`):
- `EloquentRedditThreadRepository` implements `RedditThreadRepositoryInterface`
- `EloquentRedditDraftRepository` implements `RedditDraftRepositoryInterface`
- `EloquentRedditSubredditRepository` implements `RedditSubredditRepositoryInterface`
- `EloquentRedditStrategyReportRepository` implements `RedditStrategyReportRepositoryInterface`

**Services** (`app/Infrastructure/Reddit/`):
- `RedditApiClient` implements `RedditApiInterface` — OAuth2 with auto token refresh (cached 55 min TTL), search, submit, rate limiting
- `ClaudeAiDrafter` implements `AiDrafterInterface` — Sends thread + playbook context to Claude API

**Service Provider bindings** — All port-to-implementation bindings registered in `DomainServiceProvider` (existing pattern).

### Application Layer (`app/Application/Command/Reddit/`)

- `ScoutThreads` — Search Reddit, classify threads, save new ones
- `DraftResponses` — Generate drafts for unprocessed threads
- `PublishApprovedDrafts` — Post approved drafts to Reddit
- `GenerateStrategyReport` — Analyze performance, generate report

### Artisan Commands (`app/Console/Commands/Reddit/`)

Following existing colon-separated naming convention:
- `reddit:scout` — Triggers `ScoutThreads`
- `reddit:draft` — Triggers `DraftResponses`
- `reddit:publish` — Triggers `PublishApprovedDrafts`
- `reddit:strategist` — Triggers `GenerateStrategyReport`

### HTTP Layer (`app/Http/Controllers/Admin/`)

- `RedditDashboardController` — Dashboard overview
- `RedditDraftController` — CRUD for drafts (list, show, approve, reject, retry, edit)
- `RedditSubredditController` — Manage subreddit config
- `RedditStrategyReportController` — View reports

### Views (`resources/views/admin/reddit/`)

- `dashboard.blade.php` — Overview with phase indicator, ratio chart, recent drafts
- `drafts/index.blade.php` — Pending drafts list with approve/reject/retry actions
- `drafts/show.blade.php` — Single draft with thread context, edit + approve
- `subreddits/index.blade.php` — Subreddit management
- `reports/show.blade.php` — Strategy report view

---

## 6. Scout Job Detail

**Schedule:** Every 2 hours, `withoutOverlapping()`
**Artisan:** `reddit:scout`
**Input:** Active subreddits from DB + keywords from playbook
**Process:**

1. Check `REDDIT_ENABLED` env — abort if false
2. For each active subreddit (respecting tier cadence):
   a. Search Reddit API for threads matching keywords
   b. Filter: only threads from last 24 hours, min 2 upvotes, not already in DB
   c. Classify thread type using word-boundary regex matching:
      - `\b(get|more|increase)\b.*\breview` → `how_to_get_reviews`
      - `\b(negative|bad|terrible|1.star)\b.*\breview` → `negative_review_help`
      - `\b(starting|new|launch)\b.*\bbusiness` → `starting_business`
      - `\b(tool|software|recommend|app)\b.*\breview` → `tool_recommendation`
      - `\blocal\s+seo\b|\bgoogle\s+rank` → `local_seo`
      - Default → `general`
   d. Save to `reddit_threads` with status `new`
3. Log count of new threads found

**Rate limiting:** Max 60 requests per minute (Reddit API limit). Sleep between subreddits.

---

## 7. Drafter Job Detail

**Schedule:** Daily at 07:00, `withoutOverlapping()`
**Artisan:** `reddit:draft`
**Input:** Threads with status `new` discovered within last 24 hours
**Process:**

1. Check `REDDIT_ENABLED` env — abort if false
2. Check current phase — skip if days 1-14
3. Mark threads older than 24 hours as `stale`, skip them
4. Check content mix ratio over last 30 days
5. For each unprocessed thread (max 10 per run):
   a. Check subreddit cadence — skip if over limit
   b. Determine content category based on ratio needs and thread type
   c. If phase is "comment" (days 15-30), only generate comments
   d. Build Claude API prompt (see Section 7a below)
   e. Call Claude API (model from `REDDIT_DRAFTER_MODEL` env)
   f. Save draft to `reddit_drafts` with status `pending`
   g. Update thread status to `drafted`
6. Log drafts generated

### 7a. Claude Prompt Structure

```
You are writing a Reddit comment as a knowledgeable person who helps
local businesses get more Google reviews. You are NOT a bot. You are
a real person who happens to have expertise in this area.

Rules:
- Write conversationally, like a real Reddit user
- Never mention QuickFeedback unless content_category is "brand"
- Be specific, use numbers and examples
- Reference the thread's specific situation
- Match the subreddit's culture and tone
- Keep it under 300 words for comments, 500 for posts
- End comments with an offer to elaborate, not a CTA
- {subreddit-specific rules from SubredditRules value object}

Content category: {value|discussion|brand}
Thread type: {classified type}
Subreddit: r/{name}

Thread:
Title: {title}
Body: {body}

{If brand category: "You may mention QuickFeedback naturally. Always
disclose you built it. Always mention competitors fairly. Always
provide a free/manual alternative."}

Write the response:
```

---

## 8. Publisher Job Detail

**Schedule:** Every 15 minutes, `withoutOverlapping()`, timeout 20 minutes
**Artisan:** `reddit:publish`
**Input:** Drafts with status `approved`
**Process:**

1. Check `REDDIT_ENABLED` and `REDDIT_DRY_RUN` env — abort or simulate
2. Check current phase — skip if days 1-14
3. For each approved draft (max 3 per run, to appear natural):
   a. Check subreddit cadence — skip if over limit for this week
   b. If type is `comment`: submit as reply to the thread via Reddit API
   c. If type is `post`: submit as new post to the subreddit
   d. Update draft status to `published`, save `reddit_thing_id`
   e. Wait 2-5 minutes between posts (randomized, to appear human)
4. Log published count

**Error handling:** If Reddit API returns 403/429, set draft to `failed` and log. Failed drafts can be retried from the admin dashboard (sets status back to `approved`).

---

## 9. Strategist Job Detail

**Schedule:** Weekly, Sundays at 10:00, `withoutOverlapping()`
**Artisan:** `reddit:strategist`
**Process:**

1. Gather metrics from last 7 days:
   - Drafts generated, approved, rejected, published
   - Published drafts' Reddit scores and reply counts
   - Content mix ratio (actual vs. target)
   - Per-subreddit performance
   - Phase status
2. Call Claude API (model from `REDDIT_STRATEGIST_MODEL` env — defaults to sonnet for deeper analysis)
3. Generate recommendations:
   - Which subreddits to increase/decrease activity
   - Content types that are performing well/poorly
   - New keywords to monitor
   - Phase transition readiness
4. Save report to `reddit_strategy_reports`

---

## 10. Dashboard UI

Simple Blade + Alpine.js pages behind admin auth. Matches existing app style.

### Dashboard Overview
- Current phase badge (Lurk / Comment / Full) with days remaining
- Content ratio donut chart (70/20/10 actual vs. target)
- Pending drafts count with "Review Now" button
- This week's stats: threads found, drafts created, published, total upvotes
- Latest strategy report summary

### Drafts List
- Table: subreddit, thread title (linked), content type badge, draft preview, status, actions
- Filter by: status (pending/approved/published/rejected/failed), subreddit, content type
- Bulk approve action for multiple drafts
- Each row has: Approve, Edit, Reject buttons. Failed rows have: Retry button.

### Draft Detail
- Full thread context (title, body, top comments from Reddit)
- Draft content in editable textarea
- Content category badge
- Subreddit rules reminder
- Approve / Reject / Retry buttons

### Subreddit Management
- Table: name, tier, cadence limits, active toggle, posts this week, comments this week
- Edit cadence and rules per subreddit

---

## 11. Dependencies

### New Composer Packages
- `anthropic-ai/anthropic-php` — Claude API SDK (or HTTP client directly)

### External Services
- Reddit API (free, requires app registration at reddit.com/prefs/apps)
- Claude API (Anthropic, pay-per-use)

---

## 12. Migration Plan

1. Create migrations for 4 new tables
2. Create seeder for subreddits from playbook
3. Register port bindings in `DomainServiceProvider`
4. Build domain layer (entities, value objects, port interfaces)
5. Build infrastructure (Eloquent models, repositories, Reddit API client, Claude drafter)
6. Build application commands
7. Build artisan commands + add to schedule in `routes/console.php`
8. Build admin routes behind existing auth middleware
9. Build dashboard views
10. Write tests:
    - Unit: `ContentMixPolicy`, `PhasePolicy`, `SubredditCadencePolicy`, `SubredditRules`, `ThreadType`
    - Feature: Admin dashboard controllers (CRUD, approve, reject, retry)
    - Integration: Application commands with mocked `RedditApiInterface` and `AiDrafterInterface`
11. Test end-to-end with `REDDIT_DRY_RUN=true`

---

## 13. Safety Guardrails

- **No auto-publishing** — Every draft requires human approval
- **Phase enforcement** — Cannot post during lurk phase even if manually triggered
- **No automated voting** — Lurk phase is observe-only (Reddit ToS compliance)
- **Cadence limits** — Hard caps per subreddit per week
- **Content ratio** — Drafter refuses to generate brand content if over 10%
- **Rate limiting** — Respects Reddit API limits, adds human-like delays
- **Stale thread skip** — Won't draft for threads older than 24 hours
- **withoutOverlapping()** — All jobs prevent concurrent execution
- **Dry-run mode** — `REDDIT_DRY_RUN=true` disables all Reddit API writes
- **Kill switch** — `REDDIT_ENABLED=false` disables all jobs
- **OAuth token refresh** — Cached with 55-min TTL, auto-refreshed before expiry

---

## 14. Data Retention

- `reddit_threads` with status `skipped` or `stale`: purge after 30 days
- `reddit_drafts` with status `rejected`: purge after 90 days
- `reddit_strategy_reports`: keep indefinitely
- Implemented as a scheduled cleanup in `routes/console.php` (monthly)
