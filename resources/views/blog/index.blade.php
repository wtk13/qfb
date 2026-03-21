<x-layouts.blog
    title="Blog"
    description="Tips and strategies to get more Google reviews and grow your online reputation."
    :canonical="route('blog.index')"
    :json-ld="json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Blog',
        'name' => config('app.name') . ' Blog',
        'description' => 'Tips and strategies to get more Google reviews and grow your online reputation.',
        'url' => route('blog.index'),
        'publisher' => [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
        ],
    ], JSON_UNESCAPED_SLASHES)"
>
    <div class="text-center mb-14">
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">The QFB Blog</h1>
        <p class="text-gray-500 text-lg max-w-lg mx-auto">Tips and strategies to grow your online reputation with Google reviews.</p>
    </div>

    <div class="space-y-6">
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
