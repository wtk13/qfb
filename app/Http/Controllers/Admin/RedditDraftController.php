<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use Illuminate\Http\Request;

class RedditDraftController extends Controller
{
    public function index(Request $request)
    {
        $query = RedditDraftModel::with('subreddit', 'thread');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('subreddit')) {
            $query->where('reddit_subreddit_id', $request->subreddit);
        }

        if ($request->filled('content_category')) {
            $query->where('content_category', $request->content_category);
        }

        $drafts = $query->orderByDesc('created_at')->paginate(25);

        return view('admin.reddit.drafts.index', compact('drafts'));
    }

    public function show(int $id)
    {
        $draft = RedditDraftModel::with('subreddit', 'thread')->findOrFail($id);

        return view('admin.reddit.drafts.show', compact('draft'));
    }

    public function approve(int $id)
    {
        $draft = RedditDraftModel::findOrFail($id);
        $draft->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Draft approved.');
    }

    public function reject(Request $request, int $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $draft = RedditDraftModel::findOrFail($id);
        $draft->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $request->input('reason'),
        ]);

        return back()->with('success', 'Draft rejected.');
    }

    public function retry(int $id)
    {
        $draft = RedditDraftModel::findOrFail($id);
        $draft->update(['status' => 'approved']);

        return back()->with('success', 'Draft queued for retry.');
    }

    public function update(Request $request, int $id)
    {
        $request->validate(['body' => 'required|string|max:5000']);

        $draft = RedditDraftModel::findOrFail($id);
        $draft->update(['body' => $request->input('body')]);

        return back()->with('success', 'Draft updated.');
    }
}
