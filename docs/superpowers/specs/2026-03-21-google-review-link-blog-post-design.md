# Blog Post Spec: How to Get Your Google Review Link

## Overview

New SEO-focused blog post targeting the high-intent keyword cluster around "google review link." Searchers for this term are actively building review collection infrastructure — the closest intent match to QuickFeedback's buyer persona.

## File Details

- **File:** `resources/views/blog/google-review-link.blade.php`
- **Slug:** `google-review-link`
- **Route:** `/blog/google-review-link` (existing dynamic route handles this)
- **Title:** "How to Get Your Google Review Link (3 Fast Methods for 2026)"
- **Meta description:** "Learn how to find and share your Google review link in under 2 minutes. Three step-by-step methods, plus how to create a QR code and automate review collection."
- **Canonical:** `:canonical="route('blog.show', 'google-review-link')"`
- **og-type:** `article`
- **Category:** How-To (amber badge)
- **Date:** March 21, 2026 (`datetime="2026-03-21"`, display: "March 21, 2026")
- **datePublished:** `2026-03-21`
- **dateModified:** `2026-03-21`
- **Target length:** ~2,200 words

## SEO Strategy

**Primary keywords:** "google review link", "how to get google review link"
**Secondary keywords:** "google review link generator", "create google review link", "google review QR code", "share google review link"
**Featured snippet target:** Method 1 step-by-step (numbered list format)

## Content Structure

### Opening (~75 words)
Hook: You want more Google reviews, but you can't expect customers to search for your business, scroll down, and find the review button. You need a direct link. Here are three ways to get yours in under two minutes.

### H2: What Is a Google Review Link?
- A URL that drops someone directly on your Google review form
- Why it matters: removes all friction from the review process
- Where it works: email, SMS, QR codes, print materials, website

### H2: Method 1 — From Google Search (Fastest)
Step-by-step instructions:
1. Sign into the Google account that manages your business
2. Search your exact business name on Google
3. Find the "Ask for reviews" button in your Business Profile panel
4. Copy the link from the popup

Why this is the fastest: no dashboard login required, works from any device.

### H2: Method 2 — From Google Business Profile Dashboard
Step-by-step instructions:
1. Go to business.google.com
2. Select your business (if you manage multiple)
3. On the Home tab, find the "Get more reviews" card
4. Copy the review link
5. Optionally download the native QR code Google provides here

### H2: Method 3 — Build It Manually with Your Place ID
For edge cases (unverified businesses, API integrations, etc.):
1. Go to Google's Place ID Finder
2. Search for your business
3. Copy the Place ID
4. Construct the URL: `https://search.google.com/local/writereview?placeid=YOUR_PLACE_ID`

### Inline CTA (indigo-50 background)
QuickFeedback embeds your direct Google review link automatically in every review request — no copying and pasting required.

### H2: How to Shorten Your Google Review Link
- Why long URLs look suspicious in SMS and on print materials
- Options: Bitly, Short.io, or a custom domain redirect (e.g., `review.yourbusiness.com`)
- Tip: if using in print, always test the shortened link before printing

### H2: How to Create a Google Review QR Code
- Google's native QR code feature in Business Profile dashboard
- Alternative: any QR generator (QR Code Generator, Canva) with your review link pasted in
- Where to use QR codes: receipts, business cards, table tents, invoices, vehicle wraps, in-store signage, packaging

### H2: 7 Places to Share Your Google Review Link
1. **Email signatures** — passive, every email becomes a review opportunity
2. **Post-service emails** — [internal link to email templates post]
3. **SMS messages** — highest open rates, include shortened link
4. **Your website** — footer, thank-you page, dedicated reviews page
5. **Social media bios** — Instagram, Facebook, LinkedIn
6. **Printed materials** — via QR code on cards, flyers, menus
7. **Invoices and receipts** — catches customers right after payment

### H2: The Problem with Sharing Your Link Manually
- Manual sharing is inconsistent — you forget, your team forgets
- No tracking — you don't know who was asked or who followed through
- No follow-up — one-shot requests get ~5-10% response rate
- The businesses with 300+ reviews have a system, not a link in a bookmark bar
- [Internal link to "how to ask customers for reviews after service" post]

### Final CTA (indigo-600 background, white text)
- Headline: "Turn your Google review link into an automated system"
- Body: QuickFeedback sends your review link to every customer automatically, routes happy customers to Google and catches unhappy ones privately.
- Button: "Start Your Free 14-Day Trial"
- Subtext: "No credit card required."

## Template & Format

Follows exact same Blade component structure as existing posts:
- `<x-layouts.blog>` wrapper with title, description, canonical, og-type, json-ld
- Schema.org Article structured data
- Breadcrumb navigation
- `<article class="prose prose-lg prose-gray prose-indigo ...">` content wrapper
- Two CTA blocks: mid-article (indigo-50) and end-article (indigo-600)

## Internal Linking

| From section | Links to |
|---|---|
| "Post-service emails" in sharing section | `review-request-email-template-small-business` |
| "The Problem with Sharing Manually" section | `how-to-ask-customers-for-reviews-after-service` |

## Blog Index Update

Add new card to `resources/views/blog/index.blade.php` as the first article (newest post):
- Category badge: How-To (amber)
- Date: March 21, 2026
- Title: "How to Get Your Google Review Link (3 Fast Methods for 2026)"
- Description: "Learn how to find and share your Google review link in under 2 minutes. Three step-by-step methods, plus how to create a QR code and automate review collection."

## Success Criteria

- Post renders correctly at `/blog/google-review-link`
- Blog index shows the new post as the first card
- Internal links to existing posts work
- Schema.org Article markup is valid with correct `datePublished`/`dateModified`
- Mid-article CTA links app name to homepage via `url('/')`
- Final CTA button links to `route('register')`
- Breadcrumbs are correct
- Canonical URL uses `route('blog.show', 'google-review-link')`
