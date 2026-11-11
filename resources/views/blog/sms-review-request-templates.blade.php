<x-layouts.blog
    title="5 SMS Review Request Templates That Actually Fit a Phone Screen"
    description="Most review request workflows assume the customer is at a desk. Here are five SMS templates built for specific situations - copy them, adapt them, and start sending the right message at the right moment."
    :canonical="route('blog.show', 'sms-review-request-templates')"
    og-type="article"
    :json-ld="json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => '5 SMS Review Request Templates That Actually Fit a Phone Screen',
        'description' => 'Most review request workflows assume the customer is at a desk. Here are five SMS templates built for specific situations - copy them, adapt them, and start sending the right message at the right moment.',
        'datePublished' => '2026-11-11',
        'dateModified' => '2026-11-11',
        'author' => [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
        ],
        'image' => asset('images/hero-bg.jpg'),
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => route('blog.show', 'sms-review-request-templates'),
        ],
    ], JSON_UNESCAPED_SLASHES)"
>
    <!-- Breadcrumbs -->
    <nav aria-label="Breadcrumb" class="text-sm text-gray-400 mb-8">
        <ol class="flex items-center gap-1" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="{{ url('/') }}" itemprop="item" class="hover:text-gray-600"><span itemprop="name">Home</span></a>
                <meta itemprop="position" content="1" />
            </li>
            <li class="mx-1">/</li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="{{ route('blog.index') }}" itemprop="item" class="hover:text-gray-600"><span itemprop="name">Blog</span></a>
                <meta itemprop="position" content="2" />
            </li>
            <li class="mx-1">/</li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <span itemprop="name" class="text-gray-600">5 SMS Review Request Templates That Actually Fit a Phone Screen</span>
                <meta itemprop="position" content="3" />
            </li>
        </ol>
    </nav>

    <article class="prose prose-lg prose-gray prose-indigo max-w-none prose-headings:tracking-tight prose-p:leading-relaxed prose-li:leading-relaxed prose-blockquote:border-indigo-300 prose-blockquote:bg-gray-50 prose-blockquote:rounded-r-lg prose-blockquote:py-1 prose-blockquote:pr-4">
        <time datetime="2026-11-11" class="text-sm text-gray-400 not-prose">November 11, 2026</time>
        <span class="not-prose inline-block ml-3 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Templates</span>
        <h1>5 SMS Review Request Templates That Actually Fit a Phone Screen</h1>

        <p>Most businesses send review requests that assume the customer is sitting at a desk, unhurried, next to a coffee. The reality is that your customer is probably reading your message on a small screen, one-handed, while something else is competing for their attention.</p>

        <p>Email review requests have their place. They work well for professional services and longer business relationships. But for single-visit trades, same-day services, and anything where the customer's experience is still fresh in their memory, SMS reaches people where they are - in the moment they're most likely to act.</p>

        <p>The problem is that most SMS review requests are written by people who write emails. They're too long, too formal, and too generic to do anything except get deleted - or worse, generate an opt-out.</p>

        <p>The five templates below are built for specific situations. Each one is short, references what the customer actually received, and leads directly to your review form. Take the one that fits your context, adapt the placeholders, and use it. The goal is a message that reads like it came from a person, not a marketing platform.</p>

        <h2>What Makes SMS Review Requests Fail Before the Customer Even Reads Them</h2>

        <p>Before the templates, it helps to understand why most SMS review requests don't work. The problems are almost always structural, not copy-related. A better-worded version of the same bad structure will still fail.</p>

        <p><strong>The message arrives too late.</strong> SMS works because it arrives when the experience is still warm. Send a request 72 hours after service completion and you're competing with everything else that happened in the customer's life since then. The sweet spot for most single-visit services is within 6 to 24 hours of the work being done.</p>

        <p><strong>The message is generic.</strong> "How was your experience with us today?" is not a question - it's a form letter. Customers recognize automated blasts and are conditioned to ignore them. A reference to the specific service they received takes 10 seconds to add and changes how the message reads entirely.</p>

        <p><strong>The link doesn't go to the review form.</strong> If clicking your link takes the customer to your Google Business Profile homepage and they have to find the review button themselves, most of them won't. Your link should open the review entry form directly. Test it on mobile before it goes out the first time.</p>

        <p><strong>The message is too long.</strong> SMS messages longer than 160 characters often get split across multiple parts by carriers. What reads as a tidy two-sentence message on your drafting screen can arrive as a fragmented run-on on the customer's phone. Keep it tight.</p>

        <h2>Two Things to Get Right Before the First Template Goes Out</h2>

        <p>The templates only work if two things are already in place. Neither is complicated, but skipping them turns a good template into a wasted send.</p>

        <p><strong>Timing tied to service completion, not your internal clock.</strong> Identify the moment your service is "done" from the customer's perspective. For a locksmith or a cleaning crew, that's the moment they leave. For a painter or a landscaper, it might be the next morning once the customer has had a chance to see the finished result in daylight. The trigger for your SMS should be tied to that moment - not to the end of your internal job record or invoice date.</p>

        <p><strong>A direct review link that opens the form.</strong> Generate your Google review shortlink from your Google Business Profile dashboard. The link should take the customer to a screen where they can immediately tap to leave a review - not browse your profile. If you're shortening it with a URL shortener, test the full redirect chain on a real Android and iOS device before sending at scale. Some shorteners add an intermediate redirect page that breaks the experience on mobile.</p>

        <p><strong>A note on compliance.</strong> Under the Telephone Consumer Protection Act in the United States, businesses generally need prior written consent before sending marketing texts to customers. Review requests occupy a gray area, but the prudent approach is to have customers opt in to receiving text messages at the point of booking or service intake. How this applies to your specific situation is a question for your own legal counsel - what matters here is that consent should be in place before any of these templates go out.</p>

        <h2>Template 1: The Same-Day Service Ping</h2>

        <p><strong>When to use it:</strong> Single-visit services where the work is complete and the result is immediately apparent. Plumbing, electrical, cleaning, locksmithing, appliance repair, mobile mechanic.</p>

        <p><strong>When to send it:</strong> 1 to 4 hours after the service team leaves.</p>

        <blockquote>
            <p>Hi [Name], it's [Business Name]. Hope everything is sorted with [brief description of job, e.g. "the boiler"] - give us a shout if anything comes up. If you have 60 seconds, a Google review helps a lot: [link]</p>
        </blockquote>

        <p>The message does three things. It confirms the job is done, it opens the door for follow-up issues (which signals confidence in the work), and it asks for the review as an afterthought rather than the main event. The 60-second framing is accurate - leaving a star rating and a sentence takes about that long - and it sets a low bar for the customer to clear.</p>

        <p>The job description is the part most businesses skip. "Hope the visit was helpful" is not the same as "Hope everything is sorted with the boiler." The specificity is what makes this feel like a person sent it rather than a scheduler.</p>

        <h2>Template 2: The Next-Day Follow-Up With a Soft Landing</h2>

        <p><strong>When to use it:</strong> Services where the result needs time to settle before the customer can fully assess it. Pest control, landscaping, painting, flooring installation, window replacement, garden clearance.</p>

        <p><strong>When to send it:</strong> The morning after service completion - early enough that the customer is starting their day and has seen the finished work, late enough that you're not catching them mid-morning rush.</p>

        <blockquote>
            <p>Hi [Name] - hope [the work, e.g. "the new floors"] are looking good in the daylight. If you're happy with how it turned out, we'd really appreciate a quick Google review: [link]. No pressure at all.</p>
        </blockquote>

        <p>The phrase "in the daylight" (or "in the morning," "now that it's had a chance to settle," etc.) acknowledges that the customer has been living with the result. It's not a boilerplate send - it shows the business understands the experience continues after the crew leaves.</p>

        <p>"No pressure at all" at the end is not just politeness. It signals confidence. A business that chases reviews desperately doesn't add that line. One that knows its work holds up does. The phrasing also slightly reduces the customer's sense of obligation, which paradoxically makes them more likely to act.</p>

        <h2>Template 3: The Named-Employee Ask</h2>

        <p><strong>When to use it:</strong> Relationship-based services where one person handled the whole experience. Hair and beauty, massage therapy, personal training, dental hygiene, tattoo artists, private tutors. Any service where the customer is likely to remember the staff member's name as much as the business name.</p>

        <p><strong>When to send it:</strong> Same day or the morning after. Sent from a number or profile associated with the staff member rather than a generic business number, where your platform supports it.</p>

        <blockquote>
            <p>Hi [Name], [Staff Name] from [Business Name] here - really glad I got to work with you today. If you'd take a moment to leave a quick Google review, it makes a real difference: [link]. Thanks so much.</p>
        </blockquote>

        <p>The relationship between a customer and a specific stylist, trainer, or technician is personal. A message from that person carries a different weight than a message from "the business." The first-person framing ("I got to work with you") reinforces that this is a human ask, not an automated queue.</p>

        <p>Some review collection tools, including QuickFeedback, support staff-name personalization directly in the message body - so if you're running this across a team, each request can reference the right person automatically without manually drafting each one.</p>

        <h2>Template 4: The Re-Engagement Request</h2>

        <p><strong>When to use it:</strong> A customer who used your service more than 60 days ago and hasn't been back. This message serves two purposes at once - it invites a review and it reminds the customer you exist without being transactional about it.</p>

        <p><strong>When to send it:</strong> Any day of the week, but weekday mornings tend to outperform weekend afternoons for this type of message.</p>

        <blockquote>
            <p>Hi [Name], it's been a while - we hope [the work, e.g. "the garden"] is still looking good. If you ever have a moment, a quick Google review means a lot to us: [link]. Hope to see you again soon.</p>
        </blockquote>

        <p>The tone here is warm rather than urgent. It acknowledges that time has passed, which is disarming rather than awkward. Most businesses pretend the gap didn't happen, and customers find that strange. Naming the specific work ("the garden," "your car," "the kitchen repaint") anchors the message to a real memory rather than a generic touchpoint.</p>

        <p>One caution: this is not the right template for a customer who had a problem that was never resolved. A re-engagement message to an unhappy dormant customer is a different conversation entirely and should be handled separately - ideally with a direct call or personal message before anything automated goes out.</p>

        <h2>Template 5: The Second-Touch Follow-Up</h2>

        <p><strong>When to use it:</strong> The customer received Template 1 or 2 and didn't take action within 48 to 72 hours. A second message is appropriate if you have explicit consent for follow-up texts - if you only have basic service communication consent, skip this one and check your consent process first.</p>

        <p><strong>When to send it:</strong> 48 to 72 hours after the first message, at a similar time of day as the original.</p>

        <blockquote>
            <p>Hi [Name], just a quick follow-up from [Business Name]. If you have a moment for that Google review, here's the link again: [link]. No problem at all if not - just wanted to make sure the first message got through.</p>
        </blockquote>

        <p>The last sentence does the most work. "Just wanted to make sure the first message got through" is both true and generous - it assumes the customer is not ignoring you, just busy. It gives them a face-saving way to respond now without having to explain why they didn't respond before. It also implies this is the second and final ask, not the beginning of an ongoing drip.</p>

        <p>Two messages is the ceiling. A third follow-up text asking for a review crosses from persistent into pushy, regardless of consent. If two messages don't produce a review, that customer is not in the mindset to leave one right now, and continuing is more likely to damage the relationship than produce a result.</p>

        <h2>Before You Send Any of These: One More Variable That Matters</h2>

        <p>The best template in the wrong moment will underperform a mediocre one sent at the right time. The variable that separates businesses that collect reviews consistently from those that collect them sporadically is not the message text - it's the trigger.</p>

        <p>Businesses that batch review requests - sending them all at once at the end of the week, or on the first of the month - tend to see lower conversion rates than those that send each message within hours of service completion. The reason is not mysterious: recency matters. A customer who finished their appointment three hours ago is in a different mental state than one who finished three days ago and has since had a dozen other things happen.</p>

        <p>Time of day also matters. A message that arrives at 6pm on a Tuesday, when most people have a quieter window between work and the evening, will convert differently than one that arrives at 8am on a Monday when inboxes are filling up. This is impossible to optimize manually across every customer - which is why the businesses with the strongest review collection are usually the ones that have tied the send trigger to job completion in their service software, rather than to a human's memory to send something.</p>

        <p>Use the templates above as the starting point. The trigger and the timing are what make them work at scale.</p>

        <div class="not-prose bg-indigo-600 rounded-xl p-8 my-10 text-center">
            <h3 class="text-2xl font-bold text-white">Your review link is ready. The only part left is sending it at the right moment.</h3>
            <p class="text-indigo-100 mt-2">{{ config('app.name') }} connects to your job records and sends review requests automatically when a service is marked complete - no manual sends, no forgotten customers, no batch delays.</p>
            <a href="{{ route('register') }}" class="mt-6 inline-flex items-center px-8 py-3 bg-white text-indigo-600 font-semibold rounded-xl hover:bg-indigo-50 transition shadow-lg">
                Set Up Your First Automated Request
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <p class="text-indigo-200 text-sm mt-3">No credit card required.</p>
        </div>
    </article>
</x-layouts.blog>
