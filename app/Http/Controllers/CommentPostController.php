<?php

namespace App\Http\Controllers;

use App\Models\Comment_post;
use Illuminate\Http\Request;

class CommentPostController extends Controller
{

    public function index()
    {
        //
    }



    public function store(Request $request)
    {
        $commentpost = new Comment_post();
        $commentpost->user_id = $request->input('user_id');
        $commentpost->post_id = $request->input('post_id');
        $commentpost->content = $request->input('content');
        $commentpost->save();

        return response()->json([
            'data' => $commentpost,
            'message' => 'thanh cong'
        ], 201);
    }

    public function show(string $postId)
    {
        $comments = Comment_post::where('post_id', $postId)
            ->join('users', 'comment_post.user_id', '=', 'users.id')
            ->select(
                'users.id as commenter_user_id',
                'users.name as commenter_name',
                'users.avatar as commenter_avatar',
                'comment_post.content as comment_content',
                'comment_post.created_at as comment_created_at'
            )
            ->get();

        return response()->json([
            'comments' => $comments,
        ]);
    }


    public function destroy(string $commentId)
    {
        $commentpost = Comment_post::find($commentId);

        if (!$commentpost) {
            return response()->json([
                'message' => 'commentpost not found'
            ], 404);
        }


        $commentpost->delete();


        return response()->json([
            'message' => 'xoa thanh cong ',
        ], 200);
    }
}
