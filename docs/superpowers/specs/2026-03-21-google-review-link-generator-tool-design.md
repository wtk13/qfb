# Free Tool Spec: Google Review Link Generator

## Overview

A free, client-side tool at `/tools/google-review-link-generator` that generates a direct Google review link, QR code, and copy-paste templates from a Google Place ID. Serves as a top-of-funnel growth hack — every user of this tool is QuickFeedback's ideal buyer persona.

## File Details

- **File:** `resources/views/tools/google-review-link-generator.blade.php`
- **Route:** `/tools/google-review-link-generator`
- **Title:** "Free Google Review Link Generator — Get Your Link in Seconds"
- **Meta description:** "Generate your direct Google review link instantly. Free tool — get your link, QR code, and ready-to-use email and SMS templates in seconds."
- **Canonical:** `route('tools.google-review-link-generator')`
- **datePublished:** 2026-03-21

## SEO Strategy

**Primary keywords:** "google review link generator", "free google review link generator"
**Secondary keywords:** "google review QR code generator", "create google review link", "get google review link"
**Schema markup:** WebApplication + FAQPage

## Page Structure

### Hero Section
- Headline: "Google Review Link Generator"
- Subtext: "Enter your Google Place ID and get a direct review link, QR code, and ready-to-use templates — free, instantly, no signup required."

### Tool Section (Alpine.js component)
**Input:**
- Text field for Google Place ID
- Helper text with link to Google's Place ID Finder (opens in new tab)
- "Generate My Link" button

**Results panel (shown after generation):**
1. **Review Link** — displayed in a styled box with one-click copy button
2. **QR Code** — rendered as inline SVG via pure JS, with download button
3. **Email Template** — pre-written review request email with their link embedded, copy button
4. **SMS Template** — short text message template with their link, copy button

### CTA Section
- Headline: "You have the link. Now automate sending it."
- Body: QuickFeedback sends this link to every customer automatically after each job.
- Button: "Start Your Free 14-Day Trial" → route('register')
- Subtext: "No credit card required."

### FAQ Section (with FAQPage schema)
1. "What is a Google Place ID?" — explanation + link to finder
2. "Is this tool really free?" — yes, no signup, no limits
3. "How do I use my Google review link?" — brief overview linking to blog post
4. "Can I automate sending this link?" — yes, that's what QuickFeedback does

## Technical Details

### No external dependencies
- QR code: pure JS implementation (QR matrix generation → SVG output)
- All logic runs client-side with Alpine.js
- No Google API key required
- No backend API calls

### Alpine.js Component Data
```js
{
    placeId: '',
    generated: false,
    reviewLink: '',
    copied: { link: false, email: false, sms: false },
    generate() { ... },
    copyToClipboard(text, key) { ... },
    get emailTemplate() { ... },
    get smsTemplate() { ... },
}
```

### QR Code Generation
Pure JS QR code encoder that outputs an SVG string. Embedded in the page as a script — no npm package needed. Generates a QR code for the review link URL.

## Layout

Uses the main site layout pattern (similar to welcome.blade.php) — standalone HTML page with:
- Navbar (same as homepage)
- Footer (same as homepage)
- Own `<head>` with SEO meta tags, OG tags, JSON-LD

## Internal Linking

| From | To |
|---|---|
| Blog post `google-review-link` Method 3 section | This tool |
| This tool FAQ "How do I use my review link?" | Blog post `google-review-link` |
| This tool CTA | Registration page |

## Blog Post Update

Add a link from the existing `google-review-link` blog post (Method 3 section) to this tool:
"Or use our [free Google Review Link Generator](/tools/google-review-link-generator) to do it instantly."

## Blog Index / Navigation

Add "Free Tools" link to the footer across all layouts (welcome, blog, legal).

## Success Criteria

- Tool renders at `/tools/google-review-link-generator`
- Entering a valid Place ID generates correct review link
- QR code renders and can be downloaded
- Copy buttons work for link, email template, SMS template
- CTA links to registration
- Schema.org markup is valid (WebApplication + FAQPage)
- Internal links work both ways (blog ↔ tool)
- Page is indexed (no noindex)
- Sitemap includes the new page
