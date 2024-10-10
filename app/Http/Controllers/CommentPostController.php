<?php

namespace App\Http\Controllers;

use App\Models\Comment_post;
use App\Models\User;
use Illuminate\Http\Request;

class CommentPostController extends Controller
{

    public function index()
    {
        //
    }



    public function store(Request $request)
{
    // Tạo một bình luận mới
    $commentpost = new Comment_post();
    $commentpost->user_id = $request->input('user_id');
    $commentpost->post_id = $request->input('post_id');
    $commentpost->content = $request->input('content');
    $commentpost->save();

    // Lấy thông tin người dùng để trả về
    $user = User::find($commentpost->user_id);

    return response()->json([
        'commenter_user_id' => $user->id,
        'commenter_name' => $user->name,
        'commenter_avatar' => $user->avatar, 
        'comment_id' => $commentpost->id,
        'comment_content' => $commentpost->content,
        'comment_created_at' => $commentpost->created_at,
        'message' => 'thành công'
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
                'comment_post.id as comment_id',
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
            'data' => $commentpost,
        ], 200);
    }
}
