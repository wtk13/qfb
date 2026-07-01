<x-layouts.blog
    title="Blog"
    description="Strategies and tips to get more Google reviews for your local business."
    :canonical="route('blog.index')"
    :json-ld="json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Blog',
        'name' => 'The Google Reviews Playbook',
        'description' => 'Strategies and tips to get more Google reviews for your local business.',
        'url' => route('blog.index'),
        'publisher' => [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
        ],
    ], JSON_UNESCAPED_SLASHES)"
>
    <div class="text-center mb-14">
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">The Google Reviews Playbook</h1>
        <p class="text-gray-500 text-lg max-w-lg mx-auto">Strategies and tips to get more Google reviews for your local business.</p>
    </div>

    <div class="space-y-6">
        <article>
            <a href="{{ route('blog.show', 'asking-to-update-google-review') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600">Templates</span>
                    <time datetime="2026-07-01" class="text-sm text-gray-400">July 1, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">After You Fixed the Problem: How to Ask a Customer to Update Their Google Review</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">A resolved complaint doesn't automatically update a negative review. Here are word-for-word scripts for asking a customer to revise what they wrote - and the version of this message you should never send.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'competitor-google-reviews-intelligence') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-600">Listicle</span>
                    <time datetime="2026-06-26" class="text-sm text-gray-400">June 26, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">5 Things Your Competitors' Google Reviews Are Telling You Right Now</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Your competitors' Google reviews are sitting in public and telling you exactly what customers in your market value and complain about. Here is how to read that intelligence and turn it into decisions for your own business.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'google-star-rating-how-it-works') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">Deep Dive</span>
                    <time datetime="2026-06-21" class="text-sm text-gray-400">June 21, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">Your Google Star Rating Is Not a Simple Average</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Most business owners treat their Google star rating as straightforward arithmetic - then wonder why removing a bad review barely moves the number. Here is what actually determines the displayed score, how recovery math works, and what inputs genuinely do not help.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'google-reviews-b2b-clients') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">Deep Dive</span>
                    <time datetime="2026-06-16" class="text-sm text-gray-400">June 16, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">When Your Customer Is a Business: How to Get Google Reviews From Professional Clients</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Professional clients hesitate to leave reviews for reasons that consumer-facing advice never addresses. Here is how to navigate those hesitations, find the right moment, and build a credible review profile when most of your clients are other businesses.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'google-reviews-multiple-locations') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">Deep Dive</span>
                    <time datetime="2026-06-11" class="text-sm text-gray-400">June 11, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">Managing Google Reviews Across Multiple Locations: What Changes and What Doesn't</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">A second location means a second review profile - and a second chance to fall behind. Here is what is actually different about review management for multi-location businesses, and how to keep every profile healthy.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'what-makes-google-reviews-persuasive') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-600">Listicle</span>
                    <time datetime="2026-06-06" class="text-sm text-gray-400">June 6, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">5 Details Inside a Google Review That Move Buyers Off the Fence</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Most reviews are warm, positive, and forgettable. Here are five specific details that make a review genuinely persuasive to a prospective customer - and how to help your customers include them.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'google-review-red-flags') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-600">Listicle</span>
                    <time datetime="2026-06-01" class="text-sm text-gray-400">June 1, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">6 Google Review Red Flags That Send Customers to a Competitor</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Most business owners never audit their review profile from a customer's perspective. Here are six patterns that erode trust even when your star average looks fine - and what to do about each one.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'sms-vs-email-review-requests') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-purple-50 px-3 py-1 text-xs font-medium text-purple-600">Comparison</span>
                    <time datetime="2026-05-31" class="text-sm text-gray-400">May 31, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">SMS vs. Email for Google Review Requests: What the Channel Actually Changes</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">SMS and email are not interchangeable for review requests. Here is when each channel has the structural edge, what the legal difference is, and how to sequence both for better results.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'why-customers-dont-leave-reviews') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-600">Listicle</span>
                    <time datetime="2026-05-26" class="text-sm text-gray-400">May 26, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">5 Friction Points That Stop Customers From Leaving Reviews (Even When They Mean To)</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Your happy customers say they'll leave a review and then never do. Here are the five specific points where the process breaks down - and what actually fixes each one.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'google-reviews-disappearing') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-600">Listicle</span>
                    <time datetime="2026-05-21" class="text-sm text-gray-400">May 21, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">6 Reasons Your Google Reviews Are Disappearing</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Reviews don't always stick after they're posted. Here are six concrete reasons Google removes or filters reviews - and what you can actually do about each one.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'how-to-respond-to-positive-google-reviews') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600">Templates</span>
                    <time datetime="2026-05-16" class="text-sm text-gray-400">May 16, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">Responding to 5-Star Reviews: 8 Templates That Don't Sound Like a Bot Wrote Them</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Most positive review responses are copy-pasted and generic. Here are eight scenario-specific templates organized by what the reviewer actually wrote - so your replies look like a real person read them.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'review-request-timing-customer-journey') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">Deep Dive</span>
                    <time datetime="2026-05-06" class="text-sm text-gray-400">May 6, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">Your Customer Journey Has a Natural Review Moment. Most Businesses Ask at the Wrong One.</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Generic timing advice treats payment as the end of the experience. Here is how to find the actual completion moment in your customer journey and build your review requests around it.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'google-review-situations-playbook') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600">Playbook</span>
                    <time datetime="2026-05-01" class="text-sm text-gray-400">May 1, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">6 Awkward Review Situations (and Exactly What to Do in Each One)</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">From discount demands to competitor fake review clusters: a no-nonsense playbook for the review moments that trip up business owners most often.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'anatomy-google-business-profile-converts') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-teal-50 px-3 py-1 text-xs font-medium text-teal-600">Case Study</span>
                    <time datetime="2026-04-26" class="text-sm text-gray-400">April 26, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">Anatomy of a Google Business Profile That Actually Converts</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Most Google Business Profiles are set up and forgotten. This teardown shows what separates the profiles that consistently drive calls and direction requests from the ones that just exist.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'google-review-policy-myths') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-600">Myth-busting</span>
                    <time datetime="2026-04-21" class="text-sm text-gray-400">April 21, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">5 Things Local Business Owners Get Wrong About Google's Review Policy</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Review gating, post-review incentives, spam filter surprises, response rate as a ranking signal, and the recency trap. The violations that feel most natural are the ones that put your listing at risk.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'google-reviews-vs-yelp-local-service-businesses') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-purple-50 px-3 py-1 text-xs font-medium text-purple-600">Comparison</span>
                    <time datetime="2026-04-11" class="text-sm text-gray-400">April 11, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">Google Reviews vs. Yelp for Local Service Businesses: Where to Actually Focus Your Energy</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Google Reviews and Yelp are not equivalent platforms. Understand the structural differences, where each drives real decisions, and how to stop splitting your review-collection effort in the wrong direction.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'google-reviews-restaurant') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-600">Industry Guide</span>
                    <time datetime="2026-03-31" class="text-sm text-gray-400">March 31, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">How to Get More Google Reviews for Your Restaurant (2026 Guide)</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Learn proven strategies to get more Google reviews for your restaurant. Discover when to ask guests, how to train your team, and how to build a review system that fills tables every night.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'how-to-respond-to-negative-google-reviews') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-600">Strategy</span>
                    <time datetime="2026-03-28" class="text-sm text-gray-400">March 28, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">How to Respond to Negative Google Reviews (With Examples)</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Learn how to respond to negative Google reviews professionally. Includes copy-and-paste response templates, common mistakes to avoid, and how to turn bad reviews into business wins.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'how-google-reviews-affect-local-seo') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-600">SEO</span>
                    <time datetime="2026-03-24" class="text-sm text-gray-400">March 24, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">How Google Reviews Affect Local SEO: What Small Businesses Need to Know (2026)</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Google reviews directly influence your local search rankings. Learn how review quantity, quality, recency, and response rate impact your visibility in Google Maps and local pack results.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'google-review-link') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-600">How-To</span>
                    <time datetime="2026-03-21" class="text-sm text-gray-400">March 21, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">How to Get Your Google Review Link (3 Fast Methods for 2026)</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Learn how to find and share your Google review link in under 2 minutes. Three step-by-step methods, plus how to create a QR code and automate review collection.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'review-request-email-template-small-business') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600">Templates</span>
                    <time datetime="2026-03-19" class="text-sm text-gray-400">March 19, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">Review Request Email Template for Small Business (Copy & Paste)</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Free review request email templates for small businesses. Copy-and-paste examples that actually get customers to leave Google reviews, plus tips on timing and follow-ups.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'best-way-to-collect-google-reviews-for-dentists') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-600">Industry Guide</span>
                    <time datetime="2026-03-17" class="text-sm text-gray-400">March 17, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">Best Way to Collect Google Reviews for Dentists (2026 Guide)</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Discover the best strategies for dentists to collect more Google reviews. Learn how to ask patients for reviews, automate the process, and build a 5-star dental practice reputation.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'how-to-ask-customers-for-reviews-after-service') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-600">How-To</span>
                    <time datetime="2026-03-13" class="text-sm text-gray-400">March 13, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">How to Ask Customers for Reviews After Service (With Examples)</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Learn exactly when and how to ask customers for reviews after service. Includes email templates, SMS scripts, and proven timing strategies that get more 5-star reviews.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'google-reviews-cleaning-business') }}" class="group block rounded-xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-600">Industry Guide</span>
                    <time datetime="2026-03-12" class="text-sm text-gray-400">March 12, 2026</time>
                </div>
                <h2 class="text-xl font-bold group-hover:text-indigo-600 transition">How to Get More Google Reviews for Your Cleaning Business: A Complete Guide</h2>
                <p class="text-gray-500 mt-2 leading-relaxed">Google reviews can make or break a cleaning business. Learn 8 proven strategies to consistently collect 5-star reviews and stand out from the competition.</p>
                <span class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">Read article <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </a>
        </article>
    </div>
</x-layouts.blog>
