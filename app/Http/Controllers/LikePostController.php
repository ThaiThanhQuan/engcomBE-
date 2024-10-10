<?php

namespace App\Http\Controllers;

use App\Models\Like_post;
use Illuminate\Http\Request;

class LikePostController extends Controller
{
    public function index()
    {
        //
    }

    
   
    public function store(Request $request)
    {
        $likepost = new Like_post();
        $likepost->user_id = $request->input('user_id');
        $likepost->post_id = $request->input('post_id');
        $likepost->save();

        return response()->json([
            'data' => $likepost,
            'message' => 'thanh cong mien man~'
        ], 201);
    }

   
    public function show(string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        $likepost = Like_post::find($id);

        if (!$likepost) {
            return response()->json([
                'message' => 'likepost not found'
            ], 404);
        }

        
        $likepost->delete();

    
        return response()->json([
            'message' => 'xoa thanh cong r babie',
        ], 200); 
    }
}
