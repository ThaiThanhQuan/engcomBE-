<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $comment = new Comment();
        $comment->class_id = $request->input('class_id');
        $comment->user_id = $request->input('user_id');
        $comment->parent_id = $request->input('parent_id');
        $comment->content = $request->input('content');
        $comment->save();
        return response()->json([
            'data' => $comment,
            'message' => 'Comment created successfully'
        ], 201);
    }

    public function show(string $id)
    {
        $comments = Comment::join('users', 'comments.user_id', '=', 'users.id')
            ->where('comments.class_id', $id)
            ->whereNull('comments.parent_id')
            ->select('comments.*', 'users.id as user_id', 'users.name as user_name', 'users.avatar as user_avatar')
            ->get();
        $formattedComments = $comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'class_id' => $comment->class_id,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                'user' => [
                    'id' => $comment->user_id,
                    'name' => $comment->user_name,
                    'avatar' => $comment->user_avatar,
                ],
            ];
        });
        return response()->json([
            'data' => $formattedComments,
            'message' => 'success'
        ]);
    }

    public function destroy(string $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json([
                'message' => 'Comment not found'
            ], 404);
        }
        Comment::where('parent_id', $id)->delete();
        $comment->delete();
        return response()->json([
            'message' => 'Comment and related child comments deleted successfully',
            'data' => $comment
        ], 200);
    }
    public function showResponse(string $id)
    {
        $comments = Comment::join('users', 'comments.user_id', '=', 'users.id')
            ->where('comments.class_id', $id)
            ->whereNotNull('comments.parent_id')
            ->orderBy('comments.created_at', 'asc') // Sắp xếp theo thời gian tạo
            ->select('comments.*', 'users.id as user_id', 'users.name as user_name', 'users.avatar as user_avatar')
            ->get();
    
        $formattedComments = $comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'class_id' => $comment->class_id,
                'content' => $comment->content,
                'parent_id' => $comment->parent_id,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                'user' => [
                    'id' => $comment->user_id,
                    'name' => $comment->user_name,
                    'avatar' => $comment->user_avatar,
                ],
            ];
        });
    
        return response()->json([
            'data' => $formattedComments,
            'message' => 'success'
        ]);
    }
}
