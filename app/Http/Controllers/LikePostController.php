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
        $likepost = new Like_post();
        $likepost->user_id = $request->input('user_id');
        $likepost->post_id = $request->input('post_id');
        $likepost->save();

        return response()->json([
            'data' => $likepost,
            'message' => 'success'
        ], 201);
    }

   
    public function show(string $id)
    {
        //
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
        ], 200); 
    }
}
