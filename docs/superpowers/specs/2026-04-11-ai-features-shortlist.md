# AI Features Shortlist for QuickFeedback

**Date:** 2026-04-11
**Source:** Growth Hacker agent brainstorm
**Status:** Ideation — top 3 greenlit for planning

Ranked by impact-to-effort ratio, anchored to existing Laravel 12 / DDD stack.

---

## Top 3 Picks

### 1. Negative Feedback Triage + Action Summary (S)

When 1-3 star feedback comes in, run it through Claude to extract:
- Root cause category (staff, wait time, product quality, etc.)
- Urgency signal
- Suggested response script

Render as a structured card in the dashboard instead of raw text.

- **Growth lever:** activation + retention
- **Why it wins:** Competitors show raw feedback text. This makes it actionable in 10 seconds. The single feature most likely to convert trial users who "get it" during onboarding - directly moves activation rate.
- **Domain fit:** lives inside `Domain\Feedback`

### 2. AI Review Reply Drafts (M)

After a customer submits a 4-5 star review that lands on Google, surface a one-click drafted response in the dashboard using the review text + business category as context. Business owner edits and posts.

- **Growth lever:** retention + expansion (upsell to a "Reply Assistant" add-on)
- **Why it wins:** Birdeye charges $300+/mo and buries this in enterprise plans. Can ship as a $29/mo add-on or include in a mid-tier plan to anchor pricing. High daily-habit stickiness.

### 3. Smart Review Request Personalization (M)

Before sending a campaign email, run customer name + purchase/visit context (if passed via API) through an LLM to generate a personalized first line instead of "Hi [FirstName], how was your visit?"

- **Growth lever:** acquisition (higher open/click rates lower effective CAC) + activation
- **Why it wins:** NiceJob and Podium use static templates. A measurable lift in review conversion rate is a core pitch to new prospects. A/B testable at the Campaign domain level.

---

## Next Tier

### 4. Feedback Trend Digest — weekly owner email (S)

Aggregate past 7 days of private feedback, cluster by theme using embeddings or a simple prompt, email "3 things customers mentioned most this week." Retention touchpoint without requiring login.

### 5. Multilingual Review Flow (M)

Detect browser locale or ask customer's language preference, render rating page in their language, translate submitted private feedback back to English for the owner. Unlocks hospitality/clinics in non-English markets. Competitors support this only at enterprise tier.

### 6. AI Onboarding Copilot (M)

During first-session setup: "Describe your business in one sentence." Use that to auto-fill review request email template, suggested rating page headline, and first campaign subject line. Kills the blank-page drop-off that kills trial conversions.

### 7. Competitor Review Monitor (L)

Using the already-integrated Google Places API, pull public reviews for 2-3 nearby competitors the owner specifies. Summarize their top complaints weekly. Business owners will not leave a tool that says "Your competitor is getting complaints about slow service - you mentioned speed in 4 of your last 10 positive reviews." Genuine moat. Birdeye has this at $400+/mo only.

### 8. Churn Risk Scoring (M)

Score each tenant weekly: days since last campaign sent, login frequency, review volume trend, plan age. Surface a "risk" flag in admin view. Feed into triggered email sequence ("We noticed you haven't sent a review request in 3 weeks - here's what your competitors collected this month"). No SMB competitor exposes this because they lack the PLG mindset.

### 9. Review Velocity Benchmarking (S)

Show business owner how monthly review count compares to anonymized median for their category (restaurant, salon, auto repair, etc.). "You're in the top 20% of salons on QuickFeedback" drives retention + referral. Cheap once data volume exists.

---

## Build Order Rationale

1. **Negative Feedback Triage first** - no new UI paradigm, lives entirely in `Domain\Feedback`, pays off at activation.
2. **AI Reply Drafts second** - creates daily habit loops and a clear upsell surface.
3. **Smart Personalization third** - becomes a marketing claim ("customers who use personalized requests get 34% more reviews") that feeds acquisition.

---

## Competitor References

- [Birdeye Pricing](https://birdeye.com/pricing/)
- [NiceJob Features](https://nicejob.com/features/)
- [Podium Pricing](https://www.podium.com/pricing/)
