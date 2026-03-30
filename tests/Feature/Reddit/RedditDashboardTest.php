<?php

namespace Tests\Feature\Reddit;

use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedditDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = \App\Infrastructure\Persistence\Eloquent\TenantModel::create([
            'name' => 'Test',
            'slug' => 'test-'.uniqid(),
            'trial_ends_at' => now()->addDays(14),
        ]);
        $this->admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_admin' => true,
        ]);
    }

    public function test_dashboard_loads_for_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reddit');

        $response->assertStatus(200);
    }

    public function test_dashboard_shows_stats(): void
    {
        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
        ]);

        $thread = RedditThreadModel::create([
            'reddit_subreddit_id' => $sub->id,
            'reddit_id' => 't3_test1',
            'title' => 'How to get reviews',
            'author' => 'testuser',
            'url' => 'https://reddit.com/r/test/1',
            'thread_type' => 'how_to_get_reviews',
            'status' => 'new',
            'discovered_at' => now(),
        ]);

        RedditDraftModel::create([
            'reddit_thread_id' => $thread->id,
            'reddit_subreddit_id' => $sub->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Test draft body',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/reddit');

        $response->assertStatus(200);
        $response->assertSee('How to get reviews');
    }

    public function test_non_admin_cannot_access(): void
    {
        $tenant = \App\Infrastructure\Persistence\Eloquent\TenantModel::create([
            'name' => 'Other',
            'slug' => 'other-'.uniqid(),
        ]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get('/admin/reddit');

        $response->assertStatus(403);
    }
}
