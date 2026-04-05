# Product Hunt Launch Sprint -- Design Spec

**Date:** 2026-04-05
**Status:** Approved
**Goal:** 1-week polish sprint to prepare QuickFeedback.app for a Product Hunt launch. Four deliverables: Google OAuth registration, onboarding checklist, negative feedback alerts, and PH launch prep.

---

## 1. Google OAuth Registration & Login

### Overview

Add "Continue with Google" to both registration and login pages via Laravel Socialite. Reduces signup friction from 5 fields (name, email, password, confirm password, submit) to 1 click -- critical for PH traffic where bounce rates are high.

### Dependencies

- New Composer package: `laravel/socialite`
- Google Cloud Console: OAuth 2.0 credentials (Client ID + Secret)
- Callback URL: `APP_URL/auth/google/callback`

### Environment Variables

```
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

### Migration

Add `google_id` column to `users` table:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('google_id')->nullable()->unique()->after('email');
    $table->string('password')->nullable()->change(); // Allow null for Google-only users
});
```

### Routes

```
GET  /auth/google          -> GoogleAuthController@redirect
GET  /auth/google/callback -> GoogleAuthController@callback
```

Both routes are public (guest middleware). No auth middleware.

### Controller: `GoogleAuthController`

**Redirect action:**
- Redirects to Google OAuth consent screen via Socialite

**Callback action -- three scenarios:**

1. **Existing user with `google_id` match** -- Log in, redirect to dashboard
2. **Existing user with email match but no `google_id`** -- Link Google account (set `google_id`), log in, redirect to dashboard
3. **New user** -- Create tenant + user (same as `RegisteredUserController::store()`), set `google_id`, set `password` to null, fire `Registered` event, log in, redirect to dashboard

Name is pulled from Google profile (`$googleUser->getName()`). Email from `$googleUser->getEmail()`.

### UI Changes

**Register page (`register.blade.php`):**
- Add "Continue with Google" button above the form, with a horizontal divider ("or") below it
- Button style: white background, Google "G" logo SVG, gray border, matching width to form fields

**Login page (`login.blade.php`):**
- Same "Continue with Google" button above the form with "or" divider

### Password Handling

- Users who sign up via Google have `password = null`
- The `password` column becomes nullable in the migration
- Users can set a password later via the existing profile/password update page
- Login form still works for users who have passwords
- If a Google-only user tries to log in with the email/password form, they see the standard "invalid credentials" error (acceptable -- they should use the Google button)

### Config

Register the Google Socialite driver in `config/services.php`:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
```

### Error Handling

- If Google OAuth fails (user denies consent, network error): redirect back to login with a flash error message
- If Google returns an email that exists but belongs to a different tenant: link the Google account anyway (user is the same person, just didn't have Google linked)

---

## 2. Onboarding Checklist

### Overview

New users land on a dashboard full of zeros and no guidance. Add a getting-started card that walks them through the 3 essential steps. Shows only when the user hasn't completed all steps. Dismissible.

### Checklist Steps

| Step | Check condition | Link |
|------|----------------|------|
| Create your first business profile | Tenant has >= 1 business profile | `route('business-profiles.create')` |
| Add your Google review link | Any business profile has a non-null `google_review_link` | `route('business-profiles.edit', $firstProfile)` |
| Send your first review request | Tenant has >= 1 review request sent | `route('review-requests.create', $firstProfile)` |

### Implementation

**Dashboard controller change:**
- `DashboardController::index()` also passes an `$onboarding` array with `completed` (bool) and `steps` (array of step objects with `label`, `done`, `url`)

**Query: `GetOnboardingStatus`**
- New Application query class in `app/Application/Query/GetOnboardingStatus.php`
- Accepts `tenant_id`, returns array of steps with completion status
- Checks: business profile count, any profile with google_review_link, review request count

**Dashboard view change:**
- Add a card at the top of `dashboard/index.blade.php` (above the stats grid)
- Only shown when `!$onboarding['completed']`
- Alpine.js `x-data="{ dismissed: false }"` with `x-show="!dismissed"` for dismiss button
- Dismiss is session-only (not persisted -- they'll see it again next login until they complete it)
- Each step shows a check icon (green) or empty circle (gray) + label + arrow link
- Card style: indigo-50 background, rounded, padding matches other dashboard cards

### No migration needed

All data already exists -- just querying existing tables.

---

## 3. Negative Feedback Email Alert

### Overview

When a customer submits a rating of 1-3 stars, email the business owner immediately so they can respond. This turns QuickFeedback from a passive tool into an active alert system.

### Trigger

The domain event `NegativeFeedbackReceived` already exists. Add a listener that sends a notification email to the tenant's users.

### Check existing event infrastructure

Verify `NegativeFeedbackReceived` is dispatched when a rating <= 3 is submitted. If not, add the dispatch in the rating submission flow.

### Notification

**Mailable: `NegativeFeedbackAlert`**
- Located in `app/Infrastructure/Mail/NegativeFeedbackAlert.php`
- Subject: "New [score]-star rating for [business name]"
- Body:
  - Business name
  - Rating score (with star visual)
  - Customer comment (if provided)
  - Customer email (if provided)
  - Link to view feedback in dashboard
- Sent via the configured mail driver (Resend in production)

**Listener: `SendNegativeFeedbackAlert`**
- Located in `app/Listeners/SendNegativeFeedbackAlert.php`
- Listens to `NegativeFeedbackReceived`
- Fetches all users for the tenant that owns the business profile
- Sends `NegativeFeedbackAlert` to each user
- Queued (dispatched to queue, not sent synchronously)

### Email Template

Simple, clean template matching existing email style:
- Header with business logo (if available)
- Alert banner: "You received a [score]-star rating"
- Comment block (if feedback text exists)
- CTA button: "View Feedback" linking to business profile feedback page
- Footer with QuickFeedback branding

### Registration

Register the listener in `AppServiceProvider` (following existing pattern for domain event listeners).

---

## 4. Product Hunt Launch Prep

### Overview

Product Hunt is a launch platform for tech products. A "launch" is a single day where your product is listed, the community votes (upvotes), and the top products of the day get featured. A good launch brings 500-2,000 visitors in a single day.

### Pre-Launch Checklist (1-2 weeks before)

1. **Create a PH maker account** at producthunt.com if not already done
2. **Ship page** -- create a "coming soon" page on PH to collect followers before launch day
3. **Find a hunter** (optional) -- someone with PH reputation who "hunts" (submits) your product. Improves visibility. Can self-hunt if no hunter available.
4. **Prepare assets:**
   - Logo: 240x240 PNG
   - Gallery images: 1270x760 screenshots (3-5 images showing the core flow)
   - Thumbnail/GIF: optional animated preview
5. **Schedule launch** -- PH launches at 12:01 AM PT. Tuesday-Thursday are best days.

### Listing Copy

**Tagline (60 chars max):**
"Get more Google reviews on autopilot"

**Description (~260 chars):**
"QuickFeedback sends review requests to your customers after every job. Happy customers get routed to Google. Unhappy ones leave private feedback you can act on. Set it up in 5 minutes -- no technical skills needed."

**Topics:** SaaS, Small Business, Marketing, Reviews, Customer Feedback

**First Comment (maker comment -- posted immediately after launch):**

> Hey PH! I'm Wojtek, founder of QuickFeedback.
>
> I built this because I kept hearing the same problem from local business owners: "I know reviews matter, but I never remember to ask." They'd spend thousands on ads while sitting on a goldmine of happy customers who'd leave a review if someone just sent them a link.
>
> QuickFeedback does three things:
> 1. Sends your Google review link to customers via email
> 2. Routes happy customers (4-5 stars) straight to Google
> 3. Catches unhappy customers privately so you can fix things before they go public
>
> It's $29/month with a 14-day free trial, no credit card required.
>
> I'd love your feedback -- what would make this more useful for your business?

### Launch Day Plan

**Morning (launch day, 12:01 AM PT):**
- Product goes live on PH
- Post maker comment immediately
- Share link on personal social media (LinkedIn, Twitter)

**Throughout the day:**
- Respond to every PH comment within 1 hour
- Share on relevant communities (Reddit posts where appropriate, LinkedIn)
- Email personal network with a direct ask: "I launched on Product Hunt today -- would mean a lot if you checked it out"
- Do NOT ask for upvotes (against PH rules) -- ask people to "check it out"

**After launch day:**
- Follow up with anyone who commented
- Add "Featured on Product Hunt" badge to homepage if ranked top 5
- Write a blog post about the launch experience (good for SEO + content)

### Technical Prep

Before launch day, ensure:
- Homepage loads fast (< 3s)
- Registration flow is smooth (test with Google OAuth + email)
- Onboarding checklist works for new signups
- Email delivery is working (Resend configured)
- Stripe billing is live (even if just test mode for trial signups)

---

## Success Criteria

| Item | Done when |
|------|-----------|
| Google OAuth | Can register and login via Google on both pages, creates tenant + user correctly |
| Onboarding | New user sees checklist, steps check off as completed, card hides when all done |
| Negative alerts | Rating <= 3 triggers email to business owner with score, comment, and link |
| PH listing | All copy written, assets prepared, launch scheduled |
