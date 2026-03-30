<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use Illuminate\Database\Seeder;

class RedditSubredditSeeder extends Seeder
{
    public function run(): void
    {
        $subreddits = [
            // Tier 1 -- Primary Communities (Daily)
            ['name' => 'smallbusiness', 'tier' => 1, 'max_posts_per_week' => 2, 'max_comments_per_week' => 7, 'rules_json' => ['no_direct_links' => true, 'self_promo_in_comments_only' => true], 'keywords_json' => ['get more reviews', 'negative review', 'google reviews', 'customer feedback', 'reputation management']],
            ['name' => 'Entrepreneur', 'tier' => 1, 'max_posts_per_week' => 1, 'max_comments_per_week' => 5, 'rules_json' => ['no_self_links' => true, 'weekly_promo_thread' => true], 'keywords_json' => ['customer reviews', 'feedback', 'local business', 'reputation']],
            ['name' => 'SEO', 'tier' => 1, 'max_posts_per_week' => 1, 'max_comments_per_week' => 5, 'rules_json' => ['no_promo' => true, 'case_studies_welcome' => true], 'keywords_json' => ['local seo', 'google reviews', 'review velocity', 'local pack', 'google business profile']],

            // Tier 2 -- Industry-Specific (3-4x per week)
            ['name' => 'plumbing', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 4, 'keywords_json' => ['more work', 'reputation', 'reviews', 'customers']],
            ['name' => 'dentistry', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 4, 'keywords_json' => ['patient reviews', 'practice growth', 'online reputation']],
            ['name' => 'Cleaning', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['cleaning business', 'reviews', 'reputation', 'customers']],
            ['name' => 'HVAC', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 4, 'keywords_json' => ['business growth', 'reviews', 'customers', 'reputation']],
            ['name' => 'electricians', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 4, 'keywords_json' => ['business', 'reviews', 'customers', 'reputation']],
            ['name' => 'landscaping', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['business growth', 'reviews', 'customers']],
            ['name' => 'salons', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['salon business', 'reviews', 'reputation']],
            ['name' => 'HairStylist', 'tier' => 2, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['salon', 'reviews', 'clients', 'business']],

            // Tier 3 -- Marketing and SaaS (2x per week)
            ['name' => 'marketing', 'tier' => 3, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'rules_json' => ['no_self_promo' => true], 'keywords_json' => ['reputation management', 'customer reviews', 'local marketing', 'review management']],
            ['name' => 'digital_marketing', 'tier' => 3, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['review management', 'local seo', 'reputation', 'client reviews']],
            ['name' => 'SaaS', 'tier' => 3, 'max_posts_per_week' => 1, 'max_comments_per_week' => 2, 'rules_json' => ['product_launches_in_threads' => true], 'keywords_json' => ['review tool', 'saas launch', 'customer feedback']],
            ['name' => 'startups', 'tier' => 3, 'max_posts_per_week' => 1, 'max_comments_per_week' => 2, 'rules_json' => ['strict_self_promo' => true, 'share_saturday' => true], 'keywords_json' => ['startup launch', 'feedback', 'b2b saas']],

            // Tier 4 -- Local Business and Niche (1x per week)
            ['name' => 'sweatystartup', 'tier' => 4, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['reviews', 'customers', 'reputation', 'local business']],
            ['name' => 'localbusiness', 'tier' => 4, 'max_posts_per_week' => 1, 'max_comments_per_week' => 2, 'keywords_json' => ['reviews', 'google', 'customers']],
            ['name' => 'GoogleMyBusiness', 'tier' => 4, 'max_posts_per_week' => 1, 'max_comments_per_week' => 3, 'keywords_json' => ['reviews', 'review link', 'google reviews', 'profile optimization']],
            ['name' => 'HomeImprovement', 'tier' => 4, 'max_posts_per_week' => 0, 'max_comments_per_week' => 2, 'keywords_json' => ['contractor reviews', 'finding contractor', 'recommendations']],
        ];

        foreach ($subreddits as $sub) {
            RedditSubredditModel::updateOrCreate(
                ['name' => $sub['name']],
                $sub
            );
        }
    }
}
