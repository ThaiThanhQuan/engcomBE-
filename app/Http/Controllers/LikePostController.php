<?php

namespace App\Http\Controllers;

use App\Models\Like_post;
use Illuminate\Http\Request;

class LikePostController extends Controller
{
    public function index()
    {
        $like_posts = Like_post::all();
        return response()->json([
            'data' => $like_posts,
            'message' => 'success'
        ], 200);
    }

    public function store(Request $request)
    {
        $existingLike = Like_post::where('user_id', $request->input('user_id'))
            ->where('post_id', $request->input('post_id'))
            ->first();
        if ($existingLike) {
            return response()->json([
                'message' => 'Like đã tồn tại'
            ], 409);
        }
        $likepost = new Like_post();
        $likepost->user_id = $request->input('user_id');
        $likepost->post_id = $request->input('post_id');
        $likepost->save();
        return response()->json([
            'data' => $likepost,
            'message' => 'success'
        ], 201);
    }

    public function destroy(string $likeid)
    {
        $likepost = Like_post::find($likeid);
        if (!$likepost) {
            return response()->json([
                'message' => 'likepost not found'
            ], 404);
        }
        $likepost->delete();
        return response()->json([
            'message' => 'xoa thanh cong ',
            'data' => $likepost,
        ], 200);
    }
}
