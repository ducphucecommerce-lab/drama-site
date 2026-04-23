<?php
namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Lấy danh sách comment
    public function index(Request $request)
    {
        $comments = Comment::with('user')
            ->where('drama_id', $request->drama_id)
            ->where('platform', $request->platform)
            ->where('episode', $request->episode ?? 1)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'user'       => $c->user->name ?? 'Anonymous',
                'content'    => $c->content,
                'likes'      => $c->likes,
                'time'       => $c->created_at->diffForHumans(),
                'is_vip'     => $c->user->isVip() ?? false,
                'can_delete' => auth()->check() && (auth()->id() === $c->user_id || auth()->user()->is_admin),
            ]);

        return response()->json($comments);
    }

    // Thêm comment
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Login required'], 401);
        }

        $request->validate([
            'drama_id' => 'required',
            'platform' => 'required',
            'episode'  => 'required|integer|min:1',
            'content'  => 'required|string|min:1|max:500',
        ]);

        $comment = Comment::create([
            'user_id'  => auth()->id(),
            'drama_id' => $request->drama_id,
            'platform' => $request->platform,
            'episode'  => $request->episode,
            'content'  => strip_tags(trim($request->content)),
        ]);

        $comment->load('user');

        return response()->json([
            'id'         => $comment->id,
            'user'       => $comment->user->name,
            'content'    => $comment->content,
            'likes'      => 0,
            'time'       => 'Just now',
            'is_vip'     => $comment->user->isVip(),
            'can_delete' => true,
        ]);
    }

    // Like comment
    public function like(Comment $comment)
    {
        $comment->increment('likes');
        return response()->json(['likes' => $comment->likes]);
    }

    // Xóa comment
    public function destroy(Comment $comment)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if (auth()->id() !== $comment->user_id && !auth()->user()->is_admin) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $comment->delete();
        return response()->json(['success' => true]);
    }
}
