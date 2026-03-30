<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use Illuminate\Http\Request;

class RedditSubredditController extends Controller
{
    public function index()
    {
        $subreddits = RedditSubredditModel::orderBy('tier')
            ->orderBy('name')
            ->withCount(['drafts as published_this_week' => function ($q) {
                $q->where('status', 'published')->where('published_at', '>=', now()->startOfWeek());
            }])
            ->get();

        return view('admin.reddit.subreddits.index', compact('subreddits'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'max_posts_per_week' => 'required|integer|min:0|max:20',
            'max_comments_per_week' => 'required|integer|min:0|max:50',
            'is_active' => 'boolean',
        ]);

        $subreddit = RedditSubredditModel::findOrFail($id);
        $subreddit->update($request->only('max_posts_per_week', 'max_comments_per_week', 'is_active'));

        return back()->with('success', "r/{$subreddit->name} updated.");
    }
}
