# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

QuickFeedback.app — a multi-tenant SaaS for collecting customer reviews and routing feedback. Built with Laravel 12, PHP 8.2+, Blade/Alpine.js frontend, Stripe billing via Cashier.

## Commands

```bash
# Development (runs server, queue, logs, vite concurrently)
./vendor/bin/sail composer dev

# Tests
./vendor/bin/sail composer test

# Run a single test file
./vendor/bin/sail artisan test --filter=BusinessProfileTest

# Code formatting
./vendor/bin/sail exec laravel.test ./vendor/bin/pint

# Migrations
./vendor/bin/sail artisan migrate

# All commands run through Sail (Docker)
./vendor/bin/sail up -d          # Start containers
./vendor/bin/sail artisan ...    # Any artisan command
./vendor/bin/sail npm run build  # Frontend build
```

## Architecture

Uses **Domain-Driven Design** with four layers:

- **Domain** (`src/Domain/`) — Pure business logic: entities, value objects, service classes, repository interfaces (ports), domain events. No framework dependencies. Namespaced as `Domain\`.
- **Application** (`app/Application/`) — Commands and queries that orchestrate domain objects. Each is a single invokable class.
- **Infrastructure** (`app/Infrastructure/`) — Eloquent repository implementations, mail, QR code adapters, Stripe billing service. Implements domain port interfaces.
- **HTTP** (`app/Http/`) — Controllers, middleware, form requests. Thin layer that delegates to Application commands/queries.

### Domain Boundaries

| Domain | Namespace | Covers |
|--------|-----------|--------|
| Identity | `Domain\Identity` | Users, tenants |
| Business | `Domain\Business` | Business profiles, addresses, Google review links |
| Feedback | `Domain\Feedback` | Ratings (1-5 score), text feedback, routing service |
| Campaign | `Domain\Campaign` | Review request emails, tokens |
| Billing | `Domain\Billing` | Subscription service interface |

### Key Patterns

- **Repository pattern**: Domain defines interfaces in `Port/` directories; infrastructure implements them as `Eloquent*Repository`. Bindings registered in `DomainServiceProvider`.
- **Domain events**: `RatingSubmitted`, `NegativeFeedbackReceived` — listened to in `AppServiceProvider`.
- **Multi-tenancy**: `EnsureTenantAccess` middleware isolates data per tenant. Users belong to a tenant.
- **Subscription gating**: `EnsureActiveSubscription` middleware checks Cashier subscription or trial status.

### Frontend

Server-rendered Blade templates with Alpine.js for interactivity. Vite + Tailwind CSS. No SPA framework.

## Testing

- PHPUnit with `tests/Feature/` (integration) and `tests/Unit/` (domain logic)
- Tests use SQLite in-memory database, array cache/mail/session
- Feature tests use `RefreshDatabase` trait and model factories

## External Services

- **Stripe** (Cashier) — subscriptions and billing
- **Resend** — transactional email
- **Sentry** — error monitoring
- **Google Places API** — lead scraping command (`artisan scrape:google-businesses`)