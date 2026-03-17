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
    <h1 class="text-3xl sm:text-4xl font-bold mb-4">Blog</h1>
    <p class="text-gray-600 text-lg mb-12">Tips and strategies to grow your online reputation with Google reviews.</p>

    <div class="space-y-10">
        <article>
            <a href="{{ route('blog.show', 'best-way-to-collect-google-reviews-for-dentists') }}" class="group block">
                <time datetime="2026-03-17" class="text-sm text-gray-400">March 17, 2026</time>
                <h2 class="text-xl font-bold mt-1 group-hover:text-indigo-600 transition">Best Way to Collect Google Reviews for Dentists (2026 Guide)</h2>
                <p class="text-gray-600 mt-2">Discover the best strategies for dentists to collect more Google reviews. Learn how to ask patients for reviews, automate the process, and build a 5-star dental practice reputation.</p>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'how-to-ask-customers-for-reviews-after-service') }}" class="group block">
                <time datetime="2026-03-13" class="text-sm text-gray-400">March 13, 2026</time>
                <h2 class="text-xl font-bold mt-1 group-hover:text-indigo-600 transition">How to Ask Customers for Reviews After Service (With Examples)</h2>
                <p class="text-gray-600 mt-2">Learn exactly when and how to ask customers for reviews after service. Includes email templates, SMS scripts, and proven timing strategies that get more 5-star reviews.</p>
            </a>
        </article>

        <article>
            <a href="{{ route('blog.show', 'google-reviews-cleaning-business') }}" class="group block">
                <time datetime="2026-03-12" class="text-sm text-gray-400">March 12, 2026</time>
                <h2 class="text-xl font-bold mt-1 group-hover:text-indigo-600 transition">How to Get More Google Reviews for Your Cleaning Business: A Complete Guide</h2>
                <p class="text-gray-600 mt-2">Google reviews can make or break a cleaning business. Learn 8 proven strategies to consistently collect 5-star reviews and stand out from the competition.</p>
            </a>
        </article>
    </div>
</x-layouts.blog>
