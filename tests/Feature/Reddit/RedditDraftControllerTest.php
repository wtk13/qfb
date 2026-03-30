<?php

namespace Tests\Feature\Reddit;

use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedditDraftControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private RedditSubredditModel $subreddit;
    private RedditThreadModel $thread;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = \App\Infrastructure\Persistence\Eloquent\TenantModel::create([
            'name' => 'Test',
            'slug' => 'test-' . uniqid(),
            'trial_ends_at' => now()->addDays(14),
        ]);
        $this->admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_admin' => true,
        ]);

        $this->subreddit = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
        ]);

        $this->thread = RedditThreadModel::create([
            'reddit_subreddit_id' => $this->subreddit->id,
            'reddit_id' => 't3_abc123',
            'title' => 'How to get reviews',
            'author' => 'testuser',
            'url' => 'https://reddit.com/r/smallbusiness/abc123',
            'thread_type' => 'how_to_get_reviews',
            'status' => 'drafted',
            'discovered_at' => now(),
        ]);
    }

    public function test_drafts_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reddit/drafts');

        $response->assertStatus(200);
    }

    public function test_approve_draft(): void
    {
        $draft = RedditDraftModel::create([
            'reddit_thread_id' => $this->thread->id,
            'reddit_subreddit_id' => $this->subreddit->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Great advice here...',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/reddit/drafts/{$draft->id}/approve");

        $response->assertRedirect();
        $this->assertDatabaseHas('reddit_drafts', ['id' => $draft->id, 'status' => 'approved']);
    }

    public function test_reject_draft(): void
    {
        $draft = RedditDraftModel::create([
            'reddit_thread_id' => $this->thread->id,
            'reddit_subreddit_id' => $this->subreddit->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Draft to reject',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/reddit/drafts/{$draft->id}/reject", [
            'reason' => 'Too promotional',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reddit_drafts', [
            'id' => $draft->id,
            'status' => 'rejected',
            'rejection_reason' => 'Too promotional',
        ]);
    }

    public function test_retry_failed_draft(): void
    {
        $draft = RedditDraftModel::create([
            'reddit_thread_id' => $this->thread->id,
            'reddit_subreddit_id' => $this->subreddit->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Draft that failed',
            'status' => 'failed',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/reddit/drafts/{$draft->id}/retry");

        $response->assertRedirect();
        $this->assertDatabaseHas('reddit_drafts', ['id' => $draft->id, 'status' => 'approved']);
    }

    public function test_update_draft_body(): void
    {
        $draft = RedditDraftModel::create([
            'reddit_thread_id' => $this->thread->id,
            'reddit_subreddit_id' => $this->subreddit->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Original body',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->patch("/admin/reddit/drafts/{$draft->id}", [
            'body' => 'Updated body text',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reddit_drafts', ['id' => $draft->id, 'body' => 'Updated body text']);
    }
}
